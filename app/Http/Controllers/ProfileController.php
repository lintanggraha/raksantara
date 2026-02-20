<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private const STRONG_PASSWORD_REGEX = '/^(?=.*[A-Za-z])(?=.*\d).+$/';

    // ======================
    // PROFILE PAGE
    // ======================

    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function showChangePassword()
    {
        return view('profile.change-password');
    }


    // ======================
    // UPDATE PROFILE
    // ======================

    public function update(Request $request)
    {
        $user = \App\User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Profile updated');
    }

    // ======================
    // CHANGE PASSWORD PAGE
    // ======================

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'string', 'min:8', 'regex:' . self::STRONG_PASSWORD_REGEX, 'confirmed'],
        ], [
            'new_password.regex' => 'Password baru harus mengandung kombinasi huruf dan angka.',
        ]);

        $user = \App\User::find(Auth::id());

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama salah'
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah');
    }

}
