@extends('layouts.tabler')

@section('title', 'My Profile')

@section('content_header')
    <h1>My Profile</h1>
@stop

@section('content')

<div class="row">

    <div class="col-md-6">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Update Profile</h3>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ auth()->user()->name }}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="text"
                               class="form-control"
                               value="{{ auth()->user()->email }}"
                               disabled>
                    </div>

                    <div class="form-group">
                        <label>Division</label>
                        <input type="text"
                               class="form-control"
                               value="{{ auth()->user()->division ?: '-' }}"
                               disabled>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary">
                        Update Profile
                    </button>
                </div>

            </form>

        </div>

    </div>

    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Security</h3></div>
            <div class="card-body">
                <p>Two-factor authentication adds an authenticator code or recovery code after password sign-in.</p>
                <span class="badge badge-{{ auth()->user()->hasTwoFactorEnabled() ? 'success' : 'secondary' }}">{{ auth()->user()->hasTwoFactorEnabled() ? 'MFA Enabled' : 'MFA Not Enabled' }}</span>
            </div>
            <div class="card-footer"><a class="btn btn-outline-primary" href="{{ route('two-factor.settings') }}">Manage Two-Factor Authentication</a></div>
        </div>
    </div>

</div>

@if(session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
@endif

@stop
