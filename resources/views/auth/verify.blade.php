@extends('layouts.tabler-auth')

@section('title', 'Verify Email')

@section('auth_header')
    Verifikasi Email
@stop

@section('auth_body')
    @if (session('resent'))
        <div class="alert alert-success">
            Link verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <p class="text-secondary mb-3">
        Sebelum lanjut, silakan cek email Anda untuk tautan verifikasi.
        Jika belum menerima email:
    </p>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100">Kirim Ulang Email Verifikasi</button>
    </form>
@stop
