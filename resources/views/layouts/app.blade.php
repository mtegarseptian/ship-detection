<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ShipDetect AI')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 260px;
            --primary: #0d6efd;
            --bg-sidebar: #ffffff;
            --bg-body: #f0f4f8;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background: var(--bg-body);
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid #e9ecef;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .sidebar-brand .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
        }

        .sidebar-brand h5 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
        }

        .sidebar-brand small {
            font-size: .7rem;
            color: #6c757d;
            font-weight: 400;
        }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }

        .nav-section-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #adb5bd;
            padding: .5rem 1.5rem .25rem;
            margin-top: .5rem;
        }

        .sidebar-nav .nav-link {
            color: #495057;
            padding: .6rem 1.5rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .875rem;
            font-weight: 500;
            transition: all .2s;
            border-left: 3px solid transparent;
            margin: .1rem 0;
        }

        .sidebar-nav .nav-link:hover {
            background: #f8f9fa;
            color: var(--primary);
        }

        .sidebar-nav .nav-link.active {
            background: #e8f0fe;
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .5rem;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-weight: 700;
            font-size: .875rem;
            flex-shrink: 0;
        }

        /* ── Main Content ────────────────────── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: .875rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-content { padding: 1.5rem; flex: 1; }

        /* ── Cards ───────────────────────────── */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid #e9ecef;
            height: 100%;
            transition: box-shadow .2s;
        }

        .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }

        .card { border: 1px solid #e9ecef; border-radius: 12px; }
        .card-header { background: white; border-bottom: 1px solid #e9ecef; font-weight: 600; }

        /* ── Badges & Status ─────────────────── */
        .badge-pending    { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cfe2ff; color: #084298; }
        .badge-done       { background: #d1e7dd; color: #0f5132; }
        .badge-failed     { background: #f8d7da; color: #842029; }
        .badge-active     { background: #d1e7dd; color: #0f5132; }
        .badge-inactive   { background: #e2e3e5; color: #41464b; }

        /* ── Upload Zone ─────────────────────── */
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #fafafa;
        }

        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--primary);
            background: #f0f6ff;
        }

        .upload-zone .upload-icon { font-size: 3rem; color: #adb5bd; }

        /* ── Table ───────────────────────────── */
        .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; font-weight: 600; }

        /* ── Alert ───────────────────────────── */
        .alert { border-radius: 10px; border: none; }

        /* ── Responsive ──────────────────────── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ═══════════ SIDEBAR ═══════════ --}}
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-radar"></i></div>
        <div>
            <h5>ShipDetect AI</h5>
            <small>Sistem Deteksi Kapal</small>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <a href="{{ route('detections.create') }}"
           class="nav-link {{ request()->routeIs('detections.create') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Deteksi Kapal
        </a>

        <a href="{{ route('detections.index') }}"
           class="nav-link {{ request()->routeIs('detections.index') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Riwayat Deteksi
        </a>

        {{-- 💡 PERBAIKAN: Seluruh menu Pengelolaan disembunyikan untuk User biasa --}}
        @can('admin')
        <div class="nav-section-label">Pengelolaan</div>

        <a href="{{ route('models.index') }}"
           class="nav-link {{ request()->routeIs('models.*') ? 'active' : '' }}">
            <i class="bi bi-cpu"></i> Model AI
        </a>

        <a href="{{ route('users.index') }}"
           class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Pengguna
        </a>
        @endcan
    </div>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.8rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:.7rem; color:#6c757d;">
                    {{ ucfirst(auth()->user()->role) }}
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-light" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ═══════════ MAIN ═══════════ --}}
<div id="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" id="sidebar-toggle">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h6 class="mb-0 fw-700">@yield('page-title', 'Dashboard')</h6>
                <small class="text-muted">@yield('page-subtitle', '')</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary rounded-pill">
                <i class="bi bi-circle-fill" style="font-size:.4rem;"></i>
                Sistem Online
            </span>
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
    // Sidebar toggle mobile
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>

@stack('scripts')
</body>
</html>