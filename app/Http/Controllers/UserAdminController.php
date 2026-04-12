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
        $user = auth()->user();
        $isSuper = $user->isSuperAdmin();

        $usersQuery = User::with('role');
        if (!$isSuper) {
            $usersQuery->whereHas('role', function($q) {
                $q->where('name', '!=', 'Super Admin');
            });
        }
        $users = $usersQuery->get();

        $rolesQuery = Role::query();
        if (!$isSuper) {
            $rolesQuery->where('name', '!=', 'Super Admin');
        }
        $roles = $rolesQuery->get();

        return view("admin.users.index", compact("users", "roles"));
    }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id'
        ]);

        $selectedRole = Role::find($request->role_id);
        if ($selectedRole->name === 'Super Admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, "You are not authorized to create a Super Admin.");
        }

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
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, "You cannot modify a Super Admin account.");
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id'
        ]);

        $selectedRole = Role::find($request->role_id);
        if ($selectedRole->name === 'Super Admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, "You cannot promote a user to Super Admin.");
        }
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
        $user = User::findOrFail($id);
        
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, "You cannot delete a Super Admin account.");
        }

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
