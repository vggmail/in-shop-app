<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;

class UserAdminController extends Controller {
    protected $repo;

    public function __construct(\App\Repositories\UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index() {
        $users = $this->repo->getAllWithRoles();
        $roles = Role::all();
        return view("admin.users.index", compact("users", "roles"));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::withTrashed()->where("email", $request->email)->first();
        
        if ($user) {
            if (!$user->trashed()) {
                return redirect()->back()->withErrors(['email' => 'This email is already in use by an active staff member.'])->withInput();
            }
            
            // Re-activate previously deleted user
            $user->restore();
            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'phone' => $request->phone
            ]);
            
            return redirect()->back()->with("success", "Previously deleted staff account (" . $request->email . ") has been restored.");
        }

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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id'
        ]);

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
        $isSelf = ($id == auth()->id());
        
        User::destroy($id);
        
        if ($isSelf) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->with("success", "Your account has been deleted.");
        }
        
        return redirect()->back()->with("success", "User deleted successfully");
    }
}
