<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Mail\OtpMail;
use App\Mail\TenantWelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CentralRegistrationController extends Controller
{
    /**
     * Show the step-by-step signup form.
     */
    public function showSignupForm()
    {
        return view('auth.signup');
    }

    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $otp = rand(100000, 999999);

        // Store OTP in cache/session for 10 minutes
        session(['signup_otp_' . $email => $otp]);
        session(['signup_otp_expires_' . $email => now()->addMinutes(10)]);

        // Send OTP email
        try {
            Mail::to($email)->send(new OtpMail($otp));
            \App\Models\EmailLog::log($email, 'Your OTP Code', 'sent');
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("OTP send error: " . $e->getMessage());
            \App\Models\EmailLog::log($email, 'Your OTP Code', 'failed', $e->getMessage());

            if (config('app.env') === 'local') {
                return response()->json([
                    'status' => true,
                    'message' => 'Mail server offline. [LOCAL DEV ONLY] Your OTP is: ' . $otp
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify the sent OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $cachedOtp = session('signup_otp_' . $email);
        $expiry = session('signup_otp_expires_' . $email);

        if (!$cachedOtp || !$expiry || $expiry->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired or not requested.'
            ], 422);
        }

        if ($cachedOtp != $otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP code.'
            ], 422);
        }

        // Mark verified in session
        session(['signup_email_verified_' . $email => true]);

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully!'
        ]);
    }

    /**
     * Complete the signup, provision store, send details, and return autologin URL.
     */
    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
        ], [
            'phone.regex' => 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.',
            'whatsapp_number.regex' => 'Please enter a valid 10-digit Indian WhatsApp number starting with 6, 7, 8, or 9.'
        ]);

        $email = $request->email;

        // Verify that OTP verification step was completed
        if (!session('signup_email_verified_' . $email)) {
            return response()->json([
                'status' => false,
                'message' => 'Please verify your email via OTP first.'
            ], 422);
        }

        // Generate unique subdomain from business name
        $baseSubdomain = preg_replace('/[^a-z0-9]/', '', strtolower($request->business_name));
        if (empty($baseSubdomain)) {
            $baseSubdomain = 'store';
        }
        
        $subdomain = $baseSubdomain;
        $counter = 1;
        while (Tenant::on('mysql')->where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain . $counter;
            $counter++;
        }

        $prefix = config('database.tenant_prefix', 'ovinfc6a_');
        $dbName = $prefix . $subdomain;

        // 15 days trial expiration
        $expiresAt = now()->addDays(15);

        // 1. Create central tenant entry
        $tenant = Tenant::on('mysql')->create([
            'name' => $request->business_name,
            'subdomain' => $subdomain,
            'is_active' => true,
            'expires_at' => $expiresAt,
            'phone' => $request->phone,
            'whatsapp_number' => $request->whatsapp_number
        ]);

        // 2. Create physical database
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            $tenant->forceDelete();
            \Illuminate\Support\Facades\Log::error("Dynamic DB creation failed: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to initialize workspace: ' . $e->getMessage()
            ], 500);
        }

        // 3. Switch database dynamically and run migrations
        try {
            Config::set('database.connections.tenant.database', $dbName);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Run migrations on tenant DB
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true
            ]);

            // Seed roles and initial data
            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--force' => true
            ]);

            // Create admin user in tenant DB
            $adminRole = Role::on('tenant')->where('name', 'Admin')->first();
            if (!$adminRole) {
                $adminRole = Role::on('tenant')->create(['name' => 'Admin']);
            }

            // Common default password
            $commonPassword = 'password123';

            $adminUser = User::on('tenant')->create([
                'name' => $request->owner_name,
                'email' => $email,
                'phone' => $request->phone,
                'password' => Hash::make($commonPassword),
                'role_id' => $adminRole->id,
            ]);

            // Determine URLs
            $appUrl = config('app.url', 'http://localhost');
            $parsedUrl = parse_url($appUrl);
            $host = $parsedUrl['host'] ?? 'localhost';
            if (strpos($host, 'retail.') === 0) {
                $host = substr($host, 7);
            }
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            
            $loginUrl = "{$scheme}://{$subdomain}.{$host}{$port}/login";
            
            // Send Welcome Mail with credentials
            try {
                Mail::to($email)->send(new TenantWelcomeMail($tenant, $email, $commonPassword, $loginUrl));
                \App\Models\EmailLog::log($email, 'Welcome to ' . $tenant->name, 'sent');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Welcome Email: " . $e->getMessage());
                \App\Models\EmailLog::log($email, 'Welcome to ' . $tenant->name, 'failed', $e->getMessage());
            }

            // Clear session OTP keys
            session()->forget([
                'signup_otp_' . $email,
                'signup_otp_expires_' . $email,
                'signup_email_verified_' . $email
            ]);

            // 4. Generate Auto-login signed URL
            \Illuminate\Support\Facades\URL::forceRootUrl("{$scheme}://{$subdomain}.{$host}{$port}");
            $autologinUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'central.autologin', 
                now()->addMinutes(5), 
                ['user_id' => $adminUser->id]
            );
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);

            return response()->json([
                'status' => true,
                'message' => 'Workspace created successfully!',
                'redirect_url' => $autologinUrl
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Provisioning error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status' => false,
                'message' => 'Workspace created but configuration failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
