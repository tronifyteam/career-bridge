<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — 2ne5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            width: 100%;
            max-width: 440px;
            padding: 40px 30px;
        }
        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            margin-bottom: 24px;
        }
        .brand span {
            color: #60a5fa;
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-primary:disabled {
            background: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
        }
        .validation-item {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }
        .validation-item.valid {
            color: #166534;
        }
        .validation-item.valid i {
            color: #22c55e;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <div class="brand">
        <i class="bi bi-briefcase-fill me-1"></i>Migrant<span>Work</span>TW
    </div>
    <h5 class="fw-bold text-center mb-1">Atur Ulang Kata Sandi</h5>
    <p class="text-muted text-center small mb-4">Silakan masukkan kata sandi baru Anda di bawah ini.</p>

    @if($errors->any())
        <div class="alert alert-danger p-2 small mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success p-2 small mb-3 text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ request('email') }}" required readonly>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Kata Sandi Baru</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required placeholder="Minimal 6 karakter...">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            
            {{-- Validation indicators --}}
            <div class="mt-2">
                <div class="validation-item" id="lenCheck">
                    <i class="bi bi-circle"></i> Minimal 6 karakter
                </div>
                <div class="validation-item" id="upperCheck">
                    <i class="bi bi-circle"></i> Mengandung huruf besar (A-Z)
                </div>
                <div class="validation-item" id="specialCheck">
                    <i class="bi bi-circle"></i> Mengandung karakter khusus/simbol
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi baru...">
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div class="validation-item mt-2" id="matchCheck">
                <i class="bi bi-circle"></i> Kata sandi cocok
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
            Simpan Kata Sandi Baru
        </button>
    </form>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('submitBtn');

    const lenCheck = document.getElementById('lenCheck');
    const upperCheck = document.getElementById('upperCheck');
    const specialCheck = document.getElementById('specialCheck');
    const matchCheck = document.getElementById('matchCheck');

    // Show/hide password
    const setupToggle = (btnId, inputId) => {
        document.getElementById(btnId).addEventListener('click', function() {
            const input = document.getElementById(inputId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    };
    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'password_confirmation');

    // Live validation
    const validate = () => {
        const val = passwordInput.value;
        const confirmVal = confirmInput.value;

        const hasLen = val.length >= 6;
        const hasUpper = /[A-Z]/.test(val);
        const hasSpecial = /[^a-zA-Z0-9 ]/.test(val);
        const matches = val && val === confirmVal;

        const setStatus = (el, isValid) => {
            const icon = el.querySelector('i');
            if (isValid) {
                el.classList.add('valid');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                el.classList.remove('valid');
                icon.className = 'bi bi-circle';
            }
        };

        setStatus(lenCheck, hasLen);
        setStatus(upperCheck, hasUpper);
        setStatus(specialCheck, hasSpecial);
        setStatus(matchCheck, matches);

        submitBtn.disabled = !(hasLen && hasUpper && hasSpecial && matches);
    };

    passwordInput.addEventListener('input', validate);
    confirmInput.addEventListener('input', validate);
</script>
</body>
</html>
