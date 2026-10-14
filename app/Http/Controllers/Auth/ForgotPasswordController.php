<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('sendResetLinkEmail');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        $user = User::query()
            ->where('username', (string) $request->input('username'))
            ->first();

        if (!is_null($user)) {
            $this->broker()->sendResetLink([
                'email' => (string) $user->email,
            ]);
        }

        return back()->with(
            'status',
            'Jika username terdaftar, link reset password telah dikirim.'
        );
    }
}
