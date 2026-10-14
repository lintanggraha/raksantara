@extends('layouts.tabler')

@section('title', 'Change Password')

@section('content_header')
<h1>Change Password</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('profile.changePassword') }}">
            @csrf

            {{-- CURRENT PASSWORD --}}
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="current_password" class="form-control">
            </div>

            {{-- NEW PASSWORD --}}
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" id="new_password" class="form-control">

                <small id="passwordWarning" class="text-danger" style="display:none;">
                    Password minimal 8 karakter dan harus kombinasi huruf + angka
                </small>
            </div>

            {{-- CONFIRM PASSWORD --}}
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" id="confirm_password" class="form-control">

                <small id="confirmWarning" class="text-danger" style="display:none;">
                    Password tidak sama
                </small>
            </div>

            <button class="btn btn-primary">Update Password</button>

        </form>

    </div>
</div>

@stop

@section('js')
<script>

let password = document.getElementById('new_password');
let confirmPassword = document.getElementById('confirm_password');

password.addEventListener('input', function(){

    let warning = document.getElementById('passwordWarning');
    let strongPasswordPattern = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;

    if(password.value.length > 0 && !strongPasswordPattern.test(password.value)){
        warning.style.display = 'block';
        password.classList.add('is-invalid');
    } else {
        warning.style.display = 'none';
        password.classList.remove('is-invalid');
    }

});

confirmPassword.addEventListener('input', function(){

    let warning = document.getElementById('confirmWarning');

    if(confirmPassword.value !== password.value){
        warning.style.display = 'block';
        confirmPassword.classList.add('is-invalid');
    } else {
        warning.style.display = 'none';
        confirmPassword.classList.remove('is-invalid');
    }

});

</script>
@stop

