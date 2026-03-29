<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantSettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role_id != 1) {
            abort(403, "Unauthorized access. Only Admins can modify store settings.");
        }
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();
        if (!$tenant) {
            abort(404, "Tenant not found and no default tenant exists in database.");
        }
        return view('admin.settings.tenant', compact('tenant'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            abort(403, "Unauthorized access. Only Admins can modify store settings.");
        }
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();
        if (!$tenant) {
            abort(404, "Tenant not found and no default tenant exists in database.");
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        $tenantModel = Tenant::find($tenant->id);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!file_exists(public_path('uploads/logos'))) {
                mkdir(public_path('uploads/logos'), 0777, true);
            }
            $file->move(public_path('uploads/logos'), $filename);
            $tenantModel->logo = '/uploads/logos/' . $filename;
        }

        $tenantModel->name = $request->name;
        $tenantModel->tagline = $request->tagline;
        $tenantModel->address = $request->address;
        $tenantModel->city = $request->city;
        $tenantModel->state = $request->state;
        $tenantModel->pincode = $request->pincode;
        $tenantModel->phone = $request->phone;
        $tenantModel->gst_number = $request->gst_number;
        $tenantModel->save();

        return redirect()->back()->with('success', 'Store settings updated successfully!');
    }
}
