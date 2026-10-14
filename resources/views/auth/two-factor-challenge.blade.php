@extends('layouts.tabler-auth')

@section('title', 'Verify Sign In')

@section('auth_header')
    Verify Your Sign In
@stop

@section('auth_body')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if(($method ?? 'totp') === 'email')
    <p class="text-muted">Kode verifikasi telah dikirim ke <strong>{{ $maskedEmail }}</strong>. Masukkan kode tersebut untuk melanjutkan.</p>
    <form method="POST" action="{{ route('two-factor.challenge.verify') }}" autocomplete="off">@csrf
        <label class="form-label">Kode dari Email</label>
        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" maxlength="20" required autofocus>
        <button class="btn btn-primary w-100 mt-3">Verifikasi dan Lanjutkan</button>
    </form>
    <form method="POST" action="{{ route('two-factor.challenge.resend') }}" class="mt-2">@csrf
        <button class="btn btn-link w-100">Kirim ulang kode</button>
    </form>
@else
    <p class="text-muted">Enter the six-digit code from your authenticator app, or one of your recovery codes.</p>
    <form method="POST" action="{{ route('two-factor.challenge.verify') }}" autocomplete="off">@csrf
        <label class="form-label">Authenticator or Recovery Code</label>
        <input type="text" name="code" inputmode="text" autocomplete="one-time-code" class="form-control" maxlength="20" required autofocus>
        <button class="btn btn-primary w-100 mt-3">Verify and Continue</button>
    </form>
@endif
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Cancel and sign in with another account</a>
@stop
