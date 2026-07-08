<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AutologinController extends Controller
{
    /**
     * Handle auto-login via secure signed URL.
     */
    public function autologin(Request $request, $userId)
    {
        // Check if signed URL signature is valid
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired auto-login link.');
        }

        // Retrieve user from the current tenant database
        $user = User::findOrFail($userId);

        // Log the user in
        Auth::login($user, true);

        // Regenerate session
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Logged in successfully!');
    }
}
