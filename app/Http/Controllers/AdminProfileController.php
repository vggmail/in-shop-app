<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    public function editPassword()
    {
        return view('admin.profile.password');
    }
}
