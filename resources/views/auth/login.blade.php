<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enlulu Dashboard</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --enlulu-orange: #FF6B35;
            --enlulu-orange-dark: #E55A23;
            --enlulu-dark: #1a1a1a;
            --text-light: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--enlulu-orange) 0%, #ff8c4a 50%, #ffaa6b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--enlulu-dark) 0%, #2a2a2a 100%);
            padding: 45px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 280px;
            height: 280px;
            background: rgba(255, 107, 53, 0.08);
            border-radius: 50%;
            z-index: 0;
        }
        
        .login-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .login-header-logo {
            width: 80px;
            height: auto;
            display: block;
            animation: fadeInDown 0.8s ease-out;
            margin-bottom: 5px;
        }
        
        .login-header h2 {
            font-size: 32px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.8px;
        }
        
        .login-header p {
            font-size: 13px;
            margin: 0;
            opacity: 0.8;
            font-weight: 400;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-body {
            padding: 35px 30px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--enlulu-dark);
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .form-control {
            border: 2px solid #e8e9eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: var(--enlulu-dark);
        }
        
        .form-control:focus {
            border-color: var(--enlulu-orange);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            background: white;
            outline: none;
        }
        
        .form-control::placeholder {
            color: #999;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--enlulu-orange) 0%, var(--enlulu-orange-dark) 100%);
            border: none;
            color: white;
            padding: 13px 20px;
            font-weight: 700;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-top: 6px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.25);
            letter-spacing: 0.4px;
            text-transform: capitalize;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.35);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .form-check {
            margin-top: 16px;
            margin-bottom: 24px;
        }
        
        .form-check-input {
            border: 2px solid #ddd;
            cursor: pointer;
            width: 18px;
            height: 18px;
            margin-top: 2px;
            transition: all 0.2s ease;
        }
        
        .form-check-input:checked {
            background-color: var(--enlulu-orange);
            border-color: var(--enlulu-orange);
        }
        
        .form-check-label {
            cursor: pointer;
            font-size: 13px;
            margin-left: 8px;
            color: #666;
            font-weight: 500;
        }
        
        .login-footer {
            text-align: center;
            padding: 18px 30px 22px;
            font-size: 11px;
            color: #ccc;
            border-top: 1px solid #f0f0f0;
            letter-spacing: 0.3px;
        }
        
        .login-footer a {
            color: var(--enlulu-orange);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .login-footer a:hover {
            color: var(--enlulu-orange-dark);
            text-decoration: underline;
        }
        
        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fff3f3;
            color: #dc3545;
            padding: 12px 16px;
            border-left: 4px solid #dc3545;
        }
        
        .alert-danger ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        
        .alert-danger li {
            margin-bottom: 4px;
            font-size: 13px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 22px 0;
            color: #ddd;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8e9eb;
        }
        
        .divider::before {
            margin-right: 10px;
        }
        
        .divider::after {
            margin-left: 10px;
        }
        
        .login-info {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.08) 0%, rgba(255, 107, 53, 0.04) 100%);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 10px;
            padding: 14px 16px;
            text-align: center;
            font-size: 12px;
            color: #666;
            line-height: 1.7;
        }
        
        .login-info strong {
            color: var(--enlulu-orange);
            font-weight: 600;
        }
        
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-wrapper .form-control {
            padding-right: 44px;
        }
        
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 18px;
            padding: 6px 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toggle-password-btn:hover {
            color: var(--enlulu-orange);
        }
        
        .toggle-password-btn:focus {
            outline: none;
        }
        
        @media (max-width: 480px) {
            .login-card {
                border-radius: 12px;
            }
            
            .login-header {
                padding: 35px 25px;
            }
            
            .login-body {
                padding: 28px 25px;
            }
            
            .login-header h2 {
                font-size: 28px;
            }
            
            .login-header-logo {
                width: 70px;
            }
            
            .btn-login {
                padding: 12px 16px;
                font-size: 13px;
            }
            
            .form-label {
                font-size: 12px;
            }
            
            .login-footer {
                padding: 16px 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-header-content">
                    <img src="{{ asset('images/public/ENLULU.png') }}" class="login-header-logo" alt="Enlulu Logo">
                    <h2>Enlulu</h2>
                    <p>Database Management System</p>
                </div>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong><i class="bi bi-exclamation-circle"></i> Login Gagal!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') ?? session('remembered_email') }}" 
                               placeholder="Email Anda" 
                               autocomplete="email"
                               required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="passwordInput"
                                   name="password" 
                                   value="{{ session('remembered_password') }}"
                                   placeholder="Masukkan password" 
                                   autocomplete="current-password"
                                   required>
                            <button type="button" class="toggle-password-btn" id="togglePasswordBtn" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                               {{ session('remembered_password') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Ingat saya di perangkat ini
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
                    </button>
                </form>
                
                <div class="divider">atau</div>
                
                <div class="login-info">
                    <i class="bi bi-info-circle"></i> <strong>Akun Baru?</strong><br>
                    Hubungi Super Admin untuk membuat akun
                </div>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <p style="font-size: 12px; color: #bbb;">
                    © 2026 PT. Enlulu - Database Management System
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
        
    </script>
    
    <script>
        const passwordInput = document.getElementById('passwordInput');
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Update icon
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
        }
    </script>
</body>
</html>
