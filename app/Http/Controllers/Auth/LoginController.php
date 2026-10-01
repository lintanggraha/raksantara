<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
        $this->middleware('throttle:5,1')->only('login', 'postLogin');
    }

    /**
     * Use username instead of email for authentication.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled()) {
            Auth::logout();
            $request->session()->put('mfa_user_id', $user->id);

            return redirect()->route('two-factor.challenge');
        }

        if (method_exists($user, 'isDivisionVerified') && !$user->isDivisionVerified()) {
            return redirect()->route('account.pending');
        }

        $intended = $request->session()->pull('url.intended');
        if ($intended) {
            $path = parse_url($intended, PHP_URL_PATH);
            if (in_array($path, ['/', '/home', '/dashboard', '/risk-monitoring', '/login', null], true)) {
                return redirect()->route('management.dashboard');
            }

            return redirect()->to($intended);
        }

        return redirect()->route('management.dashboard');
    }
}
