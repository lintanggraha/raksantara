<?php

namespace App\Http\Controllers;

use App\OrganizationalUnit;
use App\Role;
use App\RoleAssignment;
use App\Support\AuditLogger;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccessControlController extends Controller
{
    public function index(): View
    {
        return view('access-control.index', [
            'assignments' => RoleAssignment::query()
                ->with(['user:id,name,username,division', 'role:id,name,slug', 'organizationalUnit:id,code,name', 'assigner:id,name'])
                ->latest()
                ->paginate(25),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'username', 'division']),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug', 'description']),
            'organizationalUnits' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $assignment = RoleAssignment::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'role_id' => $validated['role_id'],
                'organizational_unit_id' => $validated['organizational_unit_id'] ?? null,
            ],
            [
                'assigned_by' => auth()->id(),
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        $assignment->load(['role', 'organizationalUnit']);
        $targetUser = User::query()->findOrFail($assignment->user_id);
        AuditLogger::logModelEvent('access.role.assigned', $targetUser, null, [
            'role' => optional($assignment->role)->slug,
            'organizational_unit' => optional($assignment->organizationalUnit)->code,
            'expires_at' => optional($assignment->expires_at)->format('Y-m-d'),
        ]);

        return redirect()->route('access-control.index')->with('success', 'Role assignment saved.');
    }

    public function destroy(RoleAssignment $assignment): RedirectResponse
    {
        $assignment->load(['user', 'role', 'organizationalUnit']);
        $targetUser = $assignment->user;
        $payload = [
            'role' => optional($assignment->role)->slug,
            'organizational_unit' => optional($assignment->organizationalUnit)->code,
        ];
        $assignment->delete();

        if ($targetUser) {
            AuditLogger::logModelEvent('access.role.revoked', $targetUser, null, $payload);
        }

        return redirect()->route('access-control.index')->with('success', 'Role assignment revoked.');
    }
}
