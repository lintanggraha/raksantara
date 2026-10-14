@extends('layouts.tabler-auth')

@section('title', 'Login')

@section('auth_header')
    Silakan login menggunakan User ID dan Password
@stop

@section('auth_body')
    @error('sso')
        <div class="alert alert-warning">{{ $message }}</div>
    @enderror

    <form action="{{ route('login') }}" method="POST" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required autofocus>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-check">
                <input type="checkbox" class="form-check-input" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span class="form-check-label">Remember Me</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>

    @if(config('sso.oidc.enabled'))
        <div class="hr-text">atau</div>
        <a href="{{ route('sso.oidc.redirect') }}" class="btn btn-outline-secondary w-100" data-sso-login>
            {{ config('sso.oidc.button_label', 'Sign in with SSO') }}
        </a>
    @endif
@stop

@section('auth_footer')
    <a href="{{ route('password.request') }}">Lupa Password?</a>
    <br>
    <small class="text-muted">Masukkan username saat reset. Untuk server offline, hubungi superadmin.</small>
    @if (Route::has('register'))
        <br>
        <a href="{{ route('register') }}">Daftar Akun Baru</a>
    @endif
@stop
