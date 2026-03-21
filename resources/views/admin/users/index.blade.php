@extends("layouts.admin")
@section("content")
<div class="d-flex justify-content-between mb-4 mt-2">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-users-cog text-danger me-2"></i> User Management</h2>
        <p class="text-muted small">Manage staff access and roles for your shop.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-user-plus small me-2"></i> Add New User</button>
    </div>
</div>

<div class="row">
    @foreach($users as $u)
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fas fa-user text-dark-50"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $u->name }}</h5>
                        <span class="badge bg-danger rounded-pill px-3 py-1 mt-1">{{ strtoupper($u->role->name) }}</span>
                    </div>
                </div>
                <p class="mb-1 small text-muted"><i class="fas fa-envelope me-2"></i> {{ $u->email }}</p>
                @if($u->phone) <p class="mb-3 small text-muted"><i class="fas fa-phone me-2"></i> {{ $u->phone }}</p> @endif
                
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark w-100 rounded-pill py-2" 
                        onclick="editUser({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', {{ $u->role_id }}, '{{ $u->phone }}')">
                        <i class="fas fa-edit small me-1"></i> Edit
                    </button>
                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="w-100">
                        @csrf @method("DELETE")
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill py-2" onclick="return confirm('Delete this user account?')"><i class="fas fa-trash small me-1"></i> Del</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Modal -->
<div class="modal fade" id="addUserModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form action="{{ route('users.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Register New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Full Name</label>
        <input type="text" name="name" class="form-control mb-3 bg-light border-0" required>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Email Address</label>
        <input type="email" name="email" class="form-control mb-3 bg-light border-0" required>

        <label class="small fw-bold text-muted text-uppercase mb-1">Mobile Number</label>
        <input type="text" name="phone" class="form-control mb-3 bg-light border-0">
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Assign Role</label>
        <select name="role_id" class="form-select mb-3 bg-light border-0" required>
            @foreach($roles as $r)<option value="{{ $r->id }}">{{ strtoupper($r->name) }}</option>@endforeach
        </select>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Temporary Password</label>
        <input type="password" name="password" class="form-control mb-3 bg-light border-0" required minlength="8">
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold">CREATE ACCOUNT</button></div>
</form></div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editUserModal"><div class="modal-dialog"><div class="modal-content border-0 shadow-lg"><form id="editForm" method="POST">
    @csrf @method("PUT")
    <div class="modal-header border-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-bold">Update Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body p-4">
        <label class="small fw-bold text-muted text-uppercase mb-1">Full Name</label>
        <input type="text" name="name" id="edit_name" class="form-control mb-3 bg-light border-0" required>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Email Address</label>
        <input type="email" name="email" id="edit_email" class="form-control mb-3 bg-light border-0" required>

        <label class="small fw-bold text-muted text-uppercase mb-1">Mobile Number</label>
        <input type="text" name="phone" id="edit_phone" class="form-control mb-3 bg-light border-0">
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Role</label>
        <select name="role_id" id="edit_role_id" class="form-select mb-3 bg-light border-0" required>
            @foreach($roles as $r)<option value="{{ $r->id }}">{{ strtoupper($r->name) }}</option>@endforeach
        </select>
        
        <label class="small fw-bold text-muted text-uppercase mb-1">Change Password (Leave blank to keep current)</label>
        <input type="password" name="password" class="form-control mb-3 bg-light border-0" minlength="8">
    </div>
    <div class="modal-footer border-0 pb-4 pt-0 px-4"><button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-pill fw-bold">SAVE UPDATES</button></div>
</form></div></div></div>

@endsection

@section("scripts")
<script>
function editUser(id, name, email, roleId, phone) {
    $("#editForm").attr("action", "/cp/users/" + id);
    $("#edit_name").val(name);
    $("#edit_email").val(email);
    $("#edit_phone").val(phone);
    $("#edit_role_id").val(roleId);
    new bootstrap.Modal(document.getElementById("editUserModal")).show();
}
</script>
@endsection
