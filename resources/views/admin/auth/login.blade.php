<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - 2ne5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: none;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="brand-logo">
        <i class="bi bi-shield-lock-fill"></i> 2ne5
    </div>
    <h5 class="mb-4 text-muted fw-normal">Admin Portal Access</h5>

    @if($errors->any())
        <div class="alert alert-danger text-start small border-0 py-2">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" class="text-start">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label text-muted small fw-semibold">Email address</label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" required autofocus placeholder="admin@2ne5.com">
        </div>
        <div class="mb-4">
            <label for="password" class="form-label text-muted small fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" id="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-100 shadow-sm">Secure Login</button>
    </form>
    
    <div class="mt-4 text-muted small">
        &copy; {{ date('Y') }} Migrant Work TW. All rights reserved.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
