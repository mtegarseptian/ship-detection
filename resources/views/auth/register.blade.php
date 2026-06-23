<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — ShipDetect AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f4f8 50%, #e8f0fe 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.08);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
        }
        .register-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }
        .register-header .logo-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.2);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
        }
        .register-body { padding: 2rem; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e9ecef;
            padding: .7rem 1rem;
            font-size: .9rem;
        }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.1); }
        .input-group-text {
            border-radius: 10px 0 0 10px !important;
            border: 1.5px solid #e9ecef;
            border-right: none;
            background: #f8f9fa;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0 !important; }
        .btn-register {
            border-radius: 10px;
            padding: .75rem;
            font-weight: 600;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border: none;
            color: white;
            transition: all .2s;
        }
        .btn-register:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(13,110,253,.3); color: white; }
    </style>
</head>
<body>

<div class="register-card my-auto">
    <div class="register-header">
        <div class="logo-icon"><i class="bi bi-radar"></i></div>
        <h4 class="mb-1 fw-700">ShipDetect AI</h4>
        <p class="mb-0 opacity-75" style="font-size:.85rem;">Sistem Deteksi Kapal Berbasis AI & Citra Satelit</p>
    </div>

    <div class="register-body">
        <h5 class="mb-1 fw-600">Buat Akun Baru</h5>
        <p class="text-muted mb-4" style="font-size:.85rem;">Daftar untuk mengakses sistem</p>

        @if($errors->any())
            <div class="alert alert-danger rounded-3" style="font-size:.875rem;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label fw-500" style="font-size:.875rem;">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="name" class="form-control" placeholder="Nama lengkap Anda"
                           value="{{ old('name') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-500" style="font-size:.875rem;">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com"
                           value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-500" style="font-size:.875rem;">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-500" style="font-size:.875rem;">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock text-muted"></i></span>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-register w-100">
                <i class="bi bi-user-plus me-2"></i>Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted" style="font-size: 0.875rem;">Sudah punya akun?</span> 
            <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #0d6efd;">Masuk di sini</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>