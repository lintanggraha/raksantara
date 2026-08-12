@extends('layouts.tabler-auth')

@section('title', 'Verify Sign In')

@section('auth_header')
    Verify Your Sign In
@stop

@section('auth_body')
<p class="text-muted">Enter the six-digit code from your authenticator app, or one of your recovery codes.</p>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('two-factor.challenge.verify') }}" autocomplete="off">@csrf
    <label class="form-label">Authenticator or Recovery Code</label>
    <input type="text" name="code" inputmode="text" autocomplete="one-time-code" class="form-control" maxlength="20" required autofocus>
    <button class="btn btn-primary w-100 mt-3">Verify and Continue</button>
</form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Cancel and sign in with another account</a>
@stop
