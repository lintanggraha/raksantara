@extends('layouts.tabler-auth')

@section('title', 'Two-Factor Authentication')

@section('auth_header')
    Two-Factor Authentication
@stop

@section('auth_body')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if($recoveryCodes)
<div class="alert alert-warning">
    <strong>Save these recovery codes now.</strong> They will not be displayed again. Each code can be used once if your authenticator is unavailable.
    <div class="mt-2 font-monospace">@foreach($recoveryCodes as $code)<div>{{ $code }}</div>@endforeach</div>
</div>
@endif

@if(!$user->hasTwoFactorEnabled())
    @if(!$pendingSecret)
        <p class="text-muted">Add a second factor to protect this account. You will use an authenticator app that supports time-based one-time passwords.</p>
        <form method="POST" action="{{ route('two-factor.setup.begin') }}">@csrf<button class="btn btn-primary w-100">Start MFA Setup</button></form>
    @else
        <div class="alert alert-info"><strong>Manual setup key</strong><br><code class="text-break">{{ $pendingSecret }}</code><br><span class="small">Add this key to your authenticator app. The provisioning URI is provided for compatible device-management workflows.</span></div>
        <div class="small text-muted mb-3 text-break">{{ $provisioningUri }}</div>
        <form method="POST" action="{{ route('two-factor.setup.confirm') }}">@csrf
            <label class="form-label">Authenticator Code</label><input name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" maxlength="20" required autofocus>
            <button class="btn btn-primary w-100 mt-3">Confirm and Enable MFA</button>
        </form>
    @endif
@else
    <div class="alert alert-success">MFA is active since {{ optional($user->two_factor_confirmed_at)->format('d M Y H:i') }}.</div>
    <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}" class="mb-3">@csrf
        <label class="form-label">Current Authenticator or Recovery Code</label><input name="code" inputmode="text" class="form-control" maxlength="20" required>
        <button class="btn btn-outline-primary w-100 mt-2">Generate New Recovery Codes</button>
    </form>
    <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Disable MFA for this account?')">@csrf @method('DELETE')
        <label class="form-label">Current Authenticator or Recovery Code</label><input name="code" inputmode="text" class="form-control" maxlength="20" required>
        <button class="btn btn-outline-danger w-100 mt-2">Disable MFA</button>
    </form>
@endif

@if($emailOtpAvailable)
    <hr class="my-4">
    <h3 class="h4">Kode Verifikasi via Email</h3>
    @if($emailOtpActive)
        <div class="alert alert-success">
            Kode verifikasi dikirim ke <strong>{{ $maskedEmail }}</strong> setiap kali Anda masuk.
            @if($emailOtpEnforced)<br><span class="small">Diwajibkan oleh kebijakan keamanan. Aktifkan aplikasi authenticator di atas untuk menggantinya.</span>@endif
        </div>
        @if(!$emailOtpEnforced)
            <form method="POST" action="{{ route('two-factor.email-otp.send') }}" class="mb-2">@csrf
                <button class="btn btn-outline-secondary w-100">Kirim Kode untuk Menonaktifkan</button>
            </form>
            @if($emailOtpCodeSent)
                <form method="POST" action="{{ route('two-factor.email-otp.disable') }}" onsubmit="return confirm('Nonaktifkan verifikasi kode email?')">@csrf @method('DELETE')
                    <label class="form-label">Kode dari Email</label><input name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" maxlength="20" required>
                    <button class="btn btn-outline-danger w-100 mt-2">Nonaktifkan Kode Email</button>
                </form>
            @endif
        @endif
    @else
        <p class="text-muted">Alternatif tanpa aplikasi authenticator: kode sekali pakai dikirim ke <strong>{{ $maskedEmail }}</strong> setiap kali Anda masuk.</p>
        <form method="POST" action="{{ route('two-factor.email-otp.send') }}" class="mb-2">@csrf
            <button class="btn btn-outline-primary w-100">Kirim Kode Verifikasi</button>
        </form>
        @if($emailOtpCodeSent)
            <form method="POST" action="{{ route('two-factor.email-otp.enable') }}">@csrf
                <label class="form-label">Kode dari Email</label><input name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" maxlength="20" required autofocus>
                <button class="btn btn-primary w-100 mt-2">Aktifkan Kode Email</button>
            </form>
        @endif
    @endif
@endif
@stop

@section('auth_footer')
    <a href="{{ route('profile.index') }}">Back to Profile</a>
@stop
