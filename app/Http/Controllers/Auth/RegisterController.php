<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\InputValidation;
use App\Support\RiskOwnerAccess;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,username'],
            'division' => ['required', 'string', 'max:255', Rule::in(RiskOwnerAccess::options())],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, underscore, dan dash.',
            'division.in' => 'Divisi yang dipilih tidak valid.',
            'password.regex' => 'Password harus mengandung kombinasi huruf dan angka.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $requestedDivision = $data['division'];
        $isBootstrapSuperadmin = (bool) config('auth.allow_bootstrap_superadmin_registration', false)
            && $this->isBootstrapSuperadminEmail($data['email'] ?? '');

        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'division' => $isBootstrapSuperadmin ? $requestedDivision : null,
            'pending_division' => $isBootstrapSuperadmin ? null : $requestedDivision,
            'division_verified_at' => $isBootstrapSuperadmin ? now() : null,
            'is_superadmin' => $isBootstrapSuperadmin,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function registered(Request $request, $user)
    {
        if (method_exists($user, 'isDivisionVerified') && !$user->isDivisionVerified()) {
            return redirect()
                ->route('account.pending')
                ->with('success', 'Registrasi berhasil. Akun Anda menunggu verifikasi divisi oleh superadmin.');
        }
    }

    private function isBootstrapSuperadminEmail(string $email): bool
    {
        $normalized = strtolower(trim($email));
        if ($normalized === '') {
            return false;
        }

        $allowed = array_values(array_filter(array_map(
            static function ($item): string {
                return strtolower(trim((string) $item));
            },
            (array) config('auth.superadmin_emails', [])
        )));

        return in_array($normalized, $allowed, true);
    }
}
