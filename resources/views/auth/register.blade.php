@extends('layouts.tabler-auth')

@section('title', 'Register')

@section('auth_header')
    Buat Akun
@stop

@section('auth_body')
    <form action="{{ route('register') }}" method="POST" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Divisi</label>
            @php($divisionOptions = \App\Support\RiskOwnerAccess::options())
            <select name="division" class="form-control @error('division') is-invalid @enderror" required>
                <option value="">Pilih Divisi</option>
                @foreach($divisionOptions as $division)
                    <option value="{{ $division }}" {{ old('division') === $division ? 'selected' : '' }}>
                        {{ $division }}
                    </option>
                @endforeach
            </select>
            @error('division')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-hint">Password minimal 8 karakter dan harus kombinasi huruf + angka.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Sudah punya akun? Login di sini</a>
@stop
