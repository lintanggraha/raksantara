<?php

namespace App\Http\Controllers;

use App\Support\RiskOwnerAccess;
use App\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:all,pending,approved',
            'search' => 'nullable|string|max:255',
        ]);

        $status = (string) $request->input('status', 'pending');
        $search = trim((string) $request->input('search', ''));

        $query = User::query()->orderByDesc('created_at');

        if ($status === 'pending') {
            $query
                ->where('is_superadmin', false)
                ->whereNull('division_verified_at');
        } elseif ($status === 'approved') {
            $query->where(function ($builder) {
                $builder
                    ->whereNotNull('division_verified_at')
                    ->orWhere('is_superadmin', true);
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(20)->appends([
            'status' => $status,
            'search' => $search,
        ]);
        $divisionOptions = RiskOwnerAccess::options();

        return view('users.index', compact('users', 'divisionOptions', 'status', 'search'));
    }

    public function updateDivision(Request $request, User $user)
    {
        $validated = $request->validate([
            'division' => ['required', 'string', 'max:255', Rule::in(RiskOwnerAccess::options())],
        ], [
            'division.in' => 'Divisi yang dipilih tidak valid.',
        ]);

        $canonicalDivision = RiskOwnerAccess::canonicalOption($validated['division']) ?? $validated['division'];

        $user->division = $canonicalDivision;
        $user->pending_division = null;
        $user->division_verified_at = now();
        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Divisi user ' . $user->username . ' berhasil diverifikasi.');
    }

    public function updateSuperadmin(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_superadmin' => ['required', 'boolean'],
        ]);

        $targetStatus = (bool) $validated['is_superadmin'];

        if ($user->is_superadmin && !$targetStatus) {
            $superadminCount = User::query()->where('is_superadmin', true)->count();
            if ($superadminCount <= 1) {
                return redirect()
                    ->back()
                    ->withErrors(['is_superadmin' => 'Tidak bisa menonaktifkan superadmin terakhir.']);
            }
        }

        $user->is_superadmin = $targetStatus;

        if ($targetStatus && is_null($user->division_verified_at)) {
            $user->division_verified_at = now();
        }

        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Status superadmin user ' . $user->username . ' berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return redirect()
                ->back()
                ->withErrors(['reset_password' => 'Gunakan menu Change Password untuk akun Anda sendiri.']);
        }

        $temporaryPassword = $this->generateTemporaryPassword();
        $before = $user->toArray();

        $user->password = Hash::make($temporaryPassword);
        $user->force_password_change = true;
        $user->password_changed_at = null;
        $user->save();

        AuditLogger::logModelEvent(
            'user.password.reset_by_superadmin',
            $user,
            $before,
            $user->fresh()->toArray()
        );

        return redirect()
            ->back()
            ->with('success', 'Password user ' . $user->username . ' berhasil direset.')
            ->with('temporary_password_username', $user->username)
            ->with('temporary_password', $temporaryPassword);
    }

    private function generateTemporaryPassword(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*_-=+';
        $maxIndex = strlen($alphabet) - 1;

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $password .= Str::upper(Str::random(1));
        }

        if (!preg_match('/\d/', $password)) {
            $password .= (string) random_int(2, 9);
        }

        return $password;
    }
}
