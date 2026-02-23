@extends('layouts.tabler-auth')

@section('title', 'Forgot Password')

@section('auth_header')
    Reset Password
@stop

@section('auth_body')
    <p class="text-secondary">
        Masukkan username akun Anda untuk mengajukan reset password.
        Jika username terdaftar, sistem akan memproses permintaan reset.
        Jika server offline/tidak terhubung email, hubungi superadmin untuk reset password sementara.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required autofocus>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Kembali ke login</a><br>
    <small class="text-muted">Tidak menerima email? Minta reset manual ke superadmin.</small>
@stop
