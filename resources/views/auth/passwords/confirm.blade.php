@extends('layouts.tabler-auth')

@section('title', 'Confirm Password')

@section('auth_header')
    Konfirmasi Password
@stop

@section('auth_body')
    <p class="text-secondary">Untuk melanjutkan, konfirmasi password Anda.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Confirm</button>
    </form>
@stop

@section('auth_footer')
    @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">Lupa Password?</a>
    @endif
@stop
