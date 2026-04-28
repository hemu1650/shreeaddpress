<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | SHREE GROUPS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        :root {
            --yellow: #F8B803;
            --yellow-hover: #E0A800;
            --dark: #1F1F1F;
        }

        body {
            background: linear-gradient(135deg, #fdf6d8, #ffffff);
            min-height: 100vh;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        .brand {
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 10px;
        }

        .brand span {
            color: var(--yellow);
        }

        .btn-yellow {
            background: var(--yellow);
            border-color: var(--yellow);
            color: #000;
            font-weight: 600;
            padding: 10px;
        }

        .btn-yellow:hover {
            background: var(--yellow-hover);
            border-color: var(--yellow-hover);
            color: #000;
        }

        .form-control:focus {
            border-color: var(--yellow);
            box-shadow: 0 0 0 0.2rem rgba(248,184,3,0.25);
        }

        .footer-text {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-card">

        <div class="brand">
            SHREE <span>GROUPS</span>
        </div>
        <p class="text-center text-muted mb-4">
            Admin Panel Login
        </p>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control"
                    required
                    autofocus
                >
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">
                        Remember me
                    </label>
                </div>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">
                        Forgot password?
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <div class="d-grid">
                <button type="submit" class="btn btn-yellow">
                    Login
                </button>
            </div>
        </form>

        <div class="footer-text">
            © {{ date('Y') }} SHREE GROUPS. All rights reserved.
        </div>

    </div>
</div>

</body>
</html>
