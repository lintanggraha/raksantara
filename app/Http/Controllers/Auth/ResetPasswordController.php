<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('reset');
    }

    public function showResetForm(Request $request, $token = null)
    {
        $email = (string) $request->input('email', '');
        $username = '';

        if ($email !== '') {
            $username = (string) optional(
                User::query()->where('email', $email)->first()
            )->username;
        }

        return view('auth.passwords.reset')->with([
            'token' => $token,
            'username' => $username,
        ]);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'username' => 'required|string|max:255|exists:users,username',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [
            'password.regex' => 'Password harus mengandung kombinasi huruf dan angka.',
        ];
    }

    protected function credentials(Request $request)
    {
        $user = User::query()
            ->where('username', (string) $request->input('username'))
            ->first();

        return [
            'email' => (string) optional($user)->email,
            'password' => (string) $request->input('password'),
            'password_confirmation' => (string) $request->input('password_confirmation'),
            'token' => (string) $request->input('token'),
        ];
    }

    /**
     * Reset the given user's password with token expiry validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Validate token hasn't expired
        $broker = $this->broker();
        $response = $broker->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }
}
