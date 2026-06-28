<div class="card-custom mb-4">
    <div class="card-header fw-semibold text-danger">
        <i class="bi bi-key me-2"></i>Change Password
    </div>
    <div class="p-3">
        <form action="{{ route('admin.users.updatePassword', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label small text-muted">New Password</label>
                <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Enter new password" required minlength="6">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm new password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Are you sure you want to change this user\'s password?');">
                Update Password
            </button>
        </form>
    </div>
</div>
