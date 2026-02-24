<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivilegeController extends Controller
{
    public function edit(User $user): View
    {
        $privilege = UserPrivilege::firstOrNew([
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
            'can_create_superviseurs' => ['boolean'],
            'can_create_employes' => ['boolean'],
            'can_delegate' => ['boolean'],
        ]);

        UserPrivilege::updateOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id],
            [
                'granted_by' => auth()->id(),
                'can_create_forms' => $validated['can_create_forms'] ?? false,
                'can_create_superviseurs' => $validated['can_create_superviseurs'] ?? false,
                'can_create_employes' => $validated['can_create_employes'] ?? false,
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
        $targetPrivilege = UserPrivilege::firstOrNew([
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
        $targetPrivilege = UserPrivilege::firstOrNew([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);

        $isSuperAdmin = $grantor->hasRole('super-admin');
        $isManager = $grantor->hasRole('manager');

        $validated = $request->validate([
            'can_create_employes' => ['boolean'],
        ]);

        if ($isSuperAdmin) {
            $fullValidated = $request->validate([
                'can_create_forms' => ['boolean'],
                'can_create_superviseurs' => ['boolean'],
                'can_create_employes' => ['boolean'],
                'can_delegate' => ['boolean'],
            ]);
            $allowed = [
                'can_create_forms' => $fullValidated['can_create_forms'] ?? false,
                'can_create_superviseurs' => $fullValidated['can_create_superviseurs'] ?? false,
                'can_create_employes' => $fullValidated['can_create_employes'] ?? false,
                'can_delegate' => $fullValidated['can_delegate'] ?? false,
            ];
        } elseif ($isManager) {
            abort_unless($user->role === 'superviseur' && $user->manager_id === $grantor->id, 403);
            $allowed = [
                'can_create_employes' => ($validated['can_create_employes'] ?? false) && ($grantorPrivilege?->can_create_employes ?? false),
            ];
        } else {
            abort(403);
        }

        UserPrivilege::updateOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id],
            [...$allowed, 'granted_by' => $grantor->id]
        );

        return redirect()->route('employees.show', $user)
            ->with('success', 'Privilèges délégués avec succès.');
    }
}
