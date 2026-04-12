<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantWelcomeMail;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        
        $tab = $request->get('tab', 'active');
        $query = Tenant::on('mysql')->orderBy('created_at', 'desc');

        if ($tab === 'archived') {
            $tenants = $query->onlyTrashed()->get();
        } else {
            $tenants = $query->get();
        }

        return view('admin.tenants.index', compact('tenants', 'tab'));
    }

    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|unique:mysql.tenants,subdomain',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'expires_at' => 'nullable|date'
        ]);

        $subdomain = strtolower($request->subdomain);
        $prefix = config('database.tenant_prefix', '');
        $dbName = $prefix . $subdomain;

        // 1. Create entry in central DB
        $tenant = Tenant::on('mysql')->create([
            'name' => $request->name,
            'subdomain' => $subdomain,
            'is_active' => true,
            'expires_at' => $request->expires_at,
        ]);

        // 2. Create physical database
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            $tenant->forceDelete(); // Rollback entry if DB creation fails
            return back()->withInput()->withErrors(['subdomain' => 'Failed to create database: ' . $e->getMessage()]);
        }

        // 3. Configure connection dynamically and run migrations
        try {
            // Backup old connection name
            $oldConnection = DB::getDefaultConnection();
            
            Config::set('database.connections.tenant.database', $dbName);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Run migrations on the new database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true
            ]);

            // Seed roles and initial data
            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--force' => true
            ]);

            // 4. Create the tenant admin user in the new database
            $adminRole = Role::on('tenant')->where('name', 'Admin')->first();
            if (!$adminRole) {
                // Fallback if seeder didn't handle roles for some reason
                $adminRole = Role::on('tenant')->create(['name' => 'Admin']);
            }

            User::on('tenant')->create([
                'name' => 'Admin - ' . $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $adminRole->id,
            ]);

            // 5. Send Welcome Email if requested
            if ($request->has('send_details')) {
                // Determine Host for login URL (use APP_URL as base)
                $appUrl = config('app.url'); // e.g. http://localhost
                $parsedUrl = parse_url($appUrl);
                $host = $parsedUrl['host'] ?? 'localhost';
                $scheme = $parsedUrl['scheme'] ?? 'http';
                $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                
                $loginUrl = "{$scheme}://{$subdomain}.{$host}{$port}/login";
                
                try {
                    Mail::to($request->email)->send(new TenantWelcomeMail($tenant, $request->email, $request->password, $loginUrl));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send tenant email: " . $e->getMessage());
                    // Don't fail the whole process if only mail fails
                    return redirect()->route('super-admin.tenants.index')->with('success', "Tenant '$subdomain' created successfully, but welcome email failed to send.");
                }
            }

            return redirect()->route('super-admin.tenants.index')->with('success', "Tenant '$subdomain' created successfully.");

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['general' => 'Critical error during setup: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $tenant = Tenant::on('mysql')->findOrFail($id);
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $tenant = Tenant::on('mysql')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|unique:mysql.tenants,subdomain,' . $id,
            'expires_at' => 'nullable|date'
        ]);

        $data = $request->only('name', 'subdomain', 'expires_at');
        $data['is_active'] = $request->has('is_active');
        
        $tenant->update($data);

        return redirect()->route('super-admin.tenants.index')->with('success', "Tenant updated successfully.");
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $tenant = Tenant::on('mysql')->findOrFail($id);
        $tenant->is_active = !$tenant->is_active;
        $tenant->save();

        return back()->with('success', "Tenant status updated successfully.");
    }

    public function destroy($id)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $tenant = Tenant::on('mysql')->findOrFail($id);
        
        // Prevent deleting the currently active tenant instance
        if (app()->bound('tenant') && app('tenant')->id == $tenant->id) {
            return back()->with('error', "Deletion blocked: You cannot delete the tenant ('{$tenant->subdomain}') you are currently logged into.");
        }

        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')->with('success', "Tenant '{$tenant->subdomain}' has been moved to archive.");
    }

    public function restore($id)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $tenant = Tenant::on('mysql')->onlyTrashed()->findOrFail($id);
        $tenant->restore();

        return redirect()->route('super-admin.tenants.index', ['tab' => 'archived'])->with('success', "Tenant '{$tenant->subdomain}' has been restored.");
    }
}
