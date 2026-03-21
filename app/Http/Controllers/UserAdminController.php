<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;

class UserAdminController extends Controller {
    public function index() {
        $users = User::with("role")->get();
        $roles = Role::all();
        return view("admin.users.index", compact("users", "roles"));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone
        ]);

        return redirect()->back()->with("success", "User added successfully");
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        if($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->role_id = $request->role_id;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with("success", "User updated successfully");
    }

    public function destroy($id) {
        if($id == auth()->id()) return redirect()->back()->with("error", "You cannot delete yourself.");
        User::destroy($id);
        return redirect()->back()->with("success", "User deleted successfully");
    }
}
