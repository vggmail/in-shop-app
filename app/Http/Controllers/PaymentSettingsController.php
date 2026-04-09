<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\Tenant;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role_id != 1) {
            abort(403, "Unauthorized access. Only Admins can modify payment settings.");
        }

        $tenant = app()->bound('tenant') ? app('tenant') : Tenant::first();
        if (!$tenant) {
            abort(404, "Tenant not found.");
        }

        // Ensure a PayU record exists for this tenant
        PaymentGateway::firstOrCreate(
            ['tenant_id' => $tenant->id, 'gateway_name' => 'PayU'],
            ['settings' => ['key' => config('services.payu.key'), 'salt' => config('services.payu.salt'), 'mode' => 'test'], 'is_active' => false]
        );

        // Fetch existing gateways for this tenant
        $gateways = PaymentGateway::where('tenant_id', $tenant->id)->get()->keyBy('gateway_name');

        return view('admin.settings.payments', compact('tenant', 'gateways'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            abort(403, "Unauthorized access. Only Admins can modify payment settings.");
        }

        $tenant = app()->bound('tenant') ? app('tenant') : Tenant::first();
        
        $request->validate([
            'payu_key' => 'nullable|string',
            'payu_salt' => 'nullable|string',
            'payu_mode' => 'nullable|in:test,live',
            'payu_active' => 'nullable|boolean',
        ]);

        // Update or Create PayU Gateway
        PaymentGateway::updateOrCreate(
            ['tenant_id' => $tenant->id, 'gateway_name' => 'PayU'],
            [
                'settings' => [
                    'key' => $request->payu_key,
                    'salt' => $request->payu_salt,
                    'mode' => $request->payu_mode ?? 'test',
                ],
                'is_active' => $request->has('payu_active') ? 1 : 0,
            ]
        );

        return redirect()->back()->with('success', 'Payment settings updated successfully!');
    }
}
