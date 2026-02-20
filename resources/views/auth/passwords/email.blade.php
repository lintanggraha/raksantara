@extends('layouts.tabler-auth')

@section('title', 'Forgot Password')

@section('auth_header')
    Reset Password
@stop

@section('auth_body')
    <p class="text-secondary">Masukkan email akun Anda, kami kirimkan link reset password.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Kembali ke login</a>
@stop
