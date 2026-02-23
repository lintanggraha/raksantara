@extends('layouts.tabler-auth')

@section('title', 'Reset Password')

@section('auth_header')
    Buat Password Baru
@stop

@section('auth_body')
    <p class="text-secondary">Gunakan username yang sama dengan akun login Anda.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" value="{{ $username ?? old('username') }}" class="form-control @error('username') is-invalid @enderror" required autofocus>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Kembali ke login</a><br>
    <small class="text-muted">Jika link reset tidak tersedia, hubungi superadmin.</small>
@stop
