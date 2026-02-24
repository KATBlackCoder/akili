<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function create(): View
    {
        $user = auth()->user();

        $creatableRoles = match ($user->role) {
            'super_admin' => ['manager', 'superviseur', 'employe'],
            'manager' => collect([
                $user->canCreateSuperviseurs() ? 'superviseur' : null,
                $user->canCreateEmployes() ? 'employe' : null,
            ])->filter()->values()->toArray(),
            'superviseur' => $user->canCreateEmployes() ? ['employe'] : [],
            default => [],
        };

        abort_if(empty($creatableRoles), 403);

        $managers = User::query()
            ->where('company_id', $user->company_id)
            ->where('role', 'manager')
            ->where('is_active', true)
            ->orderBy('lastname')
            ->get();

        $superviseurs = User::query()
            ->where('company_id', $user->company_id)
            ->where('role', 'superviseur')
            ->where('is_active', true)
            ->when(
                $user->role === 'manager',
                fn ($q) => $q->where('manager_id', $user->id)
            )
            ->when(
                $user->role === 'superviseur',
                fn ($q) => $q->where('id', $user->id)
            )
            ->orderBy('lastname')
            ->get();

        $availablePrivileges = $this->resolveAvailablePrivileges($user);

        return view('users.create', compact('creatableRoles', 'managers', 'superviseurs', 'availablePrivileges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'role' => ['required', 'in:manager,superviseur,employe'],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'manager_id' => ['required_if:role,superviseur', 'nullable', 'exists:users,id'],
            'supervisor_id' => ['required_if:role,employe', 'nullable', 'exists:users,id'],
            'privileges' => ['nullable', 'array'],
            'privileges.*' => ['in:can_create_forms,can_create_superviseurs,can_create_employes,can_delegate'],
        ]);

        $this->authorizeRoleCreation($user, $validated['role']);

        $base = strtolower($validated['lastname']).'@'.$validated['phone'].'.org';
        $username = $base;
        $i = 2;
        while (User::where('username', $username)->exists()) {
            $username = str_replace('.org', $i.'.org', $base);
            $i++;
        }
        $plainPassword = 'ML'.$validated['phone'];

        $newUser = User::create([
            'company_id' => $user->company_id,
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'phone' => $validated['phone'],
            'username' => $username,
            'password' => bcrypt($plainPassword),
            'must_change_password' => true,
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?? null,
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'is_active' => true,
        ]);

        $newUser->assignRole($validated['role']);

        if (! empty($validated['privileges'])) {
            $available = $this->resolveAvailablePrivileges($user);
            $privData = array_fill_keys([
                'can_create_forms', 'can_create_superviseurs',
                'can_create_employes', 'can_delegate',
            ], false);

            foreach ($validated['privileges'] as $priv) {
                if (in_array($priv, $available)) {
                    $privData[$priv] = true;
                }
            }

            UserPrivilege::create(array_merge([
                'company_id' => $user->company_id,
                'user_id' => $newUser->id,
                'granted_by' => $user->id,
            ], $privData));
        }

        return redirect()->route('employees.index')
            ->with('success', "Compte créé — Login : {$username} / Mot de passe : {$plainPassword}");
    }

    /** @return array<int, string> */
    private function resolveAvailablePrivileges(User $creator): array
    {
        if ($creator->role === 'super_admin') {
            return ['can_create_forms', 'can_create_superviseurs', 'can_create_employes', 'can_delegate'];
        }

        $priv = UserPrivilege::where('user_id', $creator->id)->first();
        if (! $priv) {
            return [];
        }

        return collect([
            'can_create_forms' => $priv->can_create_forms,
            'can_create_superviseurs' => $priv->can_create_superviseurs,
            'can_create_employes' => $priv->can_create_employes,
            'can_delegate' => $priv->can_delegate,
        ])->filter()->keys()->toArray();
    }

    private function authorizeRoleCreation(User $creator, string $role): void
    {
        $allowed = match ($creator->role) {
            'super_admin' => ['manager', 'superviseur', 'employe'],
            'manager' => collect([
                $creator->canCreateSuperviseurs() ? 'superviseur' : null,
                $creator->canCreateEmployes() ? 'employe' : null,
            ])->filter()->values()->toArray(),
            'superviseur' => $creator->canCreateEmployes() ? ['employe'] : [],
            default => [],
        };

        abort_unless(in_array($role, $allowed), 403);
    }
}
