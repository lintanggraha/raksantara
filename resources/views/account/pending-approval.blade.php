@extends('layouts.tabler')

@section('title', 'Pending Approval')

@section('content_header')
<h1 class="mb-0">Akun Menunggu Verifikasi</h1>
@stop

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="card shadow">
    <div class="card-body">
        <p class="mb-2">
            Halo <strong>{{ $user->name }}</strong>, akun Anda sudah terdaftar tetapi belum aktif penuh.
        </p>
        <p class="mb-2">
            Divisi yang diajukan:
            <strong>{{ $user->pending_division ?: '-' }}</strong>
        </p>
        <p class="text-muted mb-0">
            Superadmin akan memverifikasi divisi Anda terlebih dahulu sebelum akses ke modul utama dibuka.
        </p>
    </div>
</div>
@stop

