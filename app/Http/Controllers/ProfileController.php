<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use App\Support\InputValidation;
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
        $before = $user ? $user->toArray() : null;

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]);

        $user->update([
            'name' => $request->name
        ]);

        AuditLogger::logModelEvent(
            'user.profile.updated',
            $user,
            $before,
            $user->fresh()->toArray()
        );

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

        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'Password baru tidak boleh sama dengan password lama.'
            ]);
        }

        $before = $user->toArray();

        $user->password = Hash::make($request->new_password);
        $user->force_password_change = false;
        $user->password_changed_at = now();
        $user->save();

        AuditLogger::logModelEvent(
            'user.password.changed_by_self',
            $user,
            $before,
            $user->fresh()->toArray()
        );

        return back()->with('success', 'Password berhasil diubah');
    }

}
