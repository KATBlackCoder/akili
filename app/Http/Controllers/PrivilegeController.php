<?php

namespace App\Http\Controllers;

use App\Models\ManagerPrivilege;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivilegeController extends Controller
{
    public function edit(User $user): View
    {
        $privilege = ManagerPrivilege::firstOrNew([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);

        return view('privileges.edit', compact('user', 'privilege'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if(! auth()->user()->hasRole('super-admin'), 403);

        $validated = $request->validate([
            'can_create_forms' => ['boolean'],
            'can_create_users' => ['boolean'],
            'can_delegate' => ['boolean'],
        ]);

        ManagerPrivilege::updateOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id],
            [
                'granted_by' => auth()->id(),
                'can_create_forms' => $validated['can_create_forms'] ?? false,
                'can_create_users' => $validated['can_create_users'] ?? false,
                'can_delegate' => $validated['can_delegate'] ?? false,
            ]
        );

        return redirect()->route('employees.show', $user)
            ->with('success', 'Privilèges mis à jour.');
    }

    public function delegateForm(User $user): View
    {
        $grantor = auth()->user();
        abort_if(! $grantor->canDelegate(), 403);

        $grantorPrivilege = $grantor->privilege;
        $targetPrivilege = ManagerPrivilege::firstOrNew([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);

        return view('privileges.delegate', compact('user', 'grantorPrivilege', 'targetPrivilege'));
    }

    public function delegate(Request $request, User $user): RedirectResponse
    {
        $grantor = auth()->user();
        abort_if(! $grantor->canDelegate(), 403);

        $grantorPrivilege = $grantor->privilege;

        $validated = $request->validate([
            'can_create_forms' => ['boolean'],
            'can_create_users' => ['boolean'],
            'can_delegate' => ['boolean'],
        ]);

        // A manager can only grant privileges they possess themselves
        $allowed = [
            'can_create_forms' => ($validated['can_create_forms'] ?? false) && ($grantorPrivilege?->can_create_forms ?? false),
            'can_create_users' => ($validated['can_create_users'] ?? false) && ($grantorPrivilege?->can_create_users ?? false),
            'can_delegate' => ($validated['can_delegate'] ?? false) && ($grantorPrivilege?->can_delegate ?? false),
        ];

        ManagerPrivilege::updateOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id],
            [...$allowed, 'granted_by' => $grantor->id]
        );

        return redirect()->route('employees.show', $user)
            ->with('success', 'Privilèges délégués avec succès.');
    }
}
