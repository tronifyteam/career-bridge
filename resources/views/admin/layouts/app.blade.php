<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — 2ne5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-bg: #0f172a;
            --sidebar-width: 260px;
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; }
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width); background: var(--sidebar-bg);
            padding: 1.5rem 0; z-index: 100; overflow-y: auto;
        }
        .sidebar .brand {
            color: #fff; font-size: 1.25rem; font-weight: 700;
            padding: 0 1.5rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .brand span { color: #60a5fa; }
        .sidebar .nav-link {
            color: #94a3b8; padding: 0.75rem 1.5rem; display: flex;
            align-items: center; gap: 0.75rem; transition: all 0.2s;
            text-decoration: none; font-size: 0.9rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff; background: rgba(255,255,255,0.08);
        }
        .sidebar .nav-link.active { border-right: 3px solid var(--primary); }
        .sidebar .nav-link i { font-size: 1.1rem; width: 1.5rem; text-align: center; }
        .main-content { margin-left: var(--sidebar-width); padding: 2rem; min-height: 100vh; }
        .stat-card {
            background: #fff; border-radius: 12px; padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;
        }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; color: #0f172a; }
        .stat-card .stat-label { color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .card-custom {
            background: #fff; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0; overflow: hidden;
        }
        .card-custom .card-header {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem; font-weight: 600;
        }
        .badge-role {
            font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 6px;
        }
        .badge-worker { background: #dbeafe; color: #1e40af; }
        .badge-company { background: #dcfce7; color: #166534; }
        .badge-factory { background: #fef3c7; color: #92400e; }
        .badge-family_care { background: #fce7f3; color: #9d174d; }
        .badge-agency { background: #e0e7ff; color: #3730a3; }
        .badge-status-unverified { background: #f1f5f9; color: #475569; }
        .badge-status-pending { background: #fef3c7; color: #92400e; }
        .badge-status-reviewed { background: #dbeafe; color: #1e40af; }
        .badge-status-accepted { background: #dcfce7; color: #166534; }
        .badge-status-approved { background: #dcfce7; color: #166534; }
        .badge-status-verified { background: #dcfce7; color: #166534; }
        .badge-status-ready { background: #dcfce7; color: #166534; }
        .badge-status-not_ready { background: #f1f5f9; color: #475569; }
        .badge-status-rejected { background: #fee2e2; color: #991b1b; }
        .badge-status-basic_verified { background: #dcfce7; color: #166534; }
        .badge-status-manually_verified { background: #dbeafe; color: #1e40af; }
        .badge-status-submitted_for_review { background: #fef3c7; color: #92400e; }
        .badge-status-published { background: #dcfce7; color: #166534; }
        .badge-status-paused { background: #f1f5f9; color: #475569; }
        .badge-status-active { background: #dcfce7; color: #166534; }
        .badge-status-closed { background: #f1f5f9; color: #475569; }
        .badge-status-draft { background: #fef3c7; color: #92400e; }
        .badge-status-suspended { background: #f3e8ff; color: #6b21a8; }
        .table th { font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .alert-floating {
            position: fixed; top: 1rem; right: 1rem; z-index: 9999;
            min-width: 300px; animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* ── Fix: Laravel Bootstrap pagination SVG arrows ── */
        .pagination { margin-bottom: 0; }
        .pagination svg { width: 0.75rem; height: 0.75rem; display: inline; vertical-align: middle; }
        .pagination .page-link { font-size: 0.85rem; padding: 0.35rem 0.65rem; color: var(--primary); }
        .pagination .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); color: #fff; }
        .pagination .page-item.disabled .page-link { color: #94a3b8; }
    </style>
</head>
<body>
    {{-- Sidebar --}}
    <nav class="sidebar">
        <div class="brand d-flex align-items-center gap-2">
            <img src="{{ asset('images/app_logo.png') }}" alt="2ne5 Logo" style="height: 32px; width: 32px; object-fit: contain; border-radius: 6px;">
            <span>2ne5</span>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a href="{{ route('admin.workers.index') }}" class="nav-link {{ request()->routeIs('admin.workers.*') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i> Workers
                @php
                    $pendingWorkers = \App\Models\User::where('role','worker')->where('verified_badge_status','pending')->count();
                @endphp
                @if($pendingWorkers > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingWorkers }}</span>
                @endif
            </a>
            <a href="{{ route('admin.employers.index') }}" class="nav-link {{ request()->routeIs('admin.employers.*') ? 'active' : '' }}">
                <i class="bi bi-building-check"></i> Employers
                @php
                    $pendingEmployers = \App\Models\EmployerDocument::whereHas('user', function($q) {
                        $q->whereNotIn('role', ['agency', 'agency_staff']);
                    })->where('status','pending')->count();
                @endphp
                @if($pendingEmployers > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingEmployers }}</span>
                @endif
            </a>
            <a href="{{ route('admin.agencies.index') }}" class="nav-link {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Agencies
                @php
                    $pendingAgencies = \App\Models\EmployerDocument::whereHas('user', function($q) {
                        $q->whereIn('role', ['agency', 'agency_staff']);
                    })->where('status','pending')->count();
                @endphp
                @if($pendingAgencies > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingAgencies }}</span>
                @endif
            </a>
            <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i> Jobs
            </a>
            <a href="{{ route('admin.applications.index') }}" class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Applications
            </a>
            
            <div class="px-4 pt-3 pb-1 text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em; opacity: 0.8;">Platform Control</div>
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-flag"></i> Reports
            </a>
            <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Payments
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                <i class="bi bi-star"></i> Subscriptions
            </a>
            <a href="{{ route('admin.advertisements.index') }}" class="nav-link {{ request()->routeIs('admin.advertisements.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone"></i> Advertisements
            </a>
            <a href="{{ route('admin.audit_logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> Audit Logs
            </a>

            <div class="px-4 pt-3 pb-1 text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em; opacity: 0.8;">Master Data</div>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('admin.cities.index') }}" class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Cities
            </a>
            <a href="{{ route('admin.skills.index') }}" class="nav-link {{ request()->routeIs('admin.skills.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i> Skills
            </a>
            <a href="{{ route('admin.industries.index') }}" class="nav-link {{ request()->routeIs('admin.industries.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Industries
            </a>
            <a href="{{ route('admin.languages.index') }}" class="nav-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                <i class="bi bi-translate"></i> Languages
            </a>
            <a href="{{ route('admin.nationalities.index') }}" class="nav-link {{ request()->routeIs('admin.nationalities.*') ? 'active' : '' }}">
                <i class="bi bi-globe"></i> Nationalities
            </a>
        </div>
        <div class="mt-auto px-3 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 2rem;">
            @if(auth()->check())
            <div class="mb-3">
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light w-100 text-start border-0" style="color: #94a3b8;">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
            @endif
            <div class="text-muted small">
                <i class="bi bi-database"></i> {{ config('database.default') === 'pgsql' ? 'PostgreSQL' : (config('database.default') === 'sqlite' ? 'SQLite' : ucfirst(config('database.default'))) }} Database<br>
                <span class="text-white-50">{{ config('app.name') }} v1.0</span>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-floating alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-floating alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-floating alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Validation failed:</strong>
                <ul class="mb-0 mt-1 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
