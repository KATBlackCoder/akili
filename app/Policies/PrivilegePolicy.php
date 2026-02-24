<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPrivilege;

class PrivilegePolicy
{
    /**
     * Super Admin peut tout accorder.
     * Manager peut accorder can_create_employes à un Superviseur de sa branche (s'il le possède).
     * Superviseur ne peut rien déléguer.
     */
    public function delegate(User $grantor, UserPrivilege $targetPrivilege): bool
    {
        if ($grantor->hasRole('super-admin')) {
            return true;
        }

        if (! $grantor->hasRole('manager')) {
            return false;
        }

        $beneficiary = $targetPrivilege->user;

        if (! $beneficiary) {
            return false;
        }

        if ($beneficiary->role !== 'superviseur') {
            return false;
        }

        return $beneficiary->manager_id === $grantor->id;
    }

    /**
     * Seuls les privilèges délégables par un Manager à un Superviseur.
     *
     * @return array<string, bool>
     */
    public function delegatablePrivileges(User $grantor, UserPrivilege $targetPrivilege): array
    {
        if ($grantor->hasRole('super-admin')) {
            return [
                'can_create_forms' => true,
                'can_create_superviseurs' => true,
                'can_create_employes' => true,
                'can_delegate' => true,
            ];
        }

        $grantorPrivilege = $grantor->privilege;

        return [
            'can_create_employes' => $grantorPrivilege?->can_create_employes ?? false,
        ];
    }
}
