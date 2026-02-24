<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserPrivilege;

class UserPrivilegeObserver
{
    /**
     * Révocation en cascade : si un Manager perd can_create_employes,
     * tous ses Superviseurs perdent également ce privilège.
     */
    public function updated(UserPrivilege $privilege): void
    {
        if ($privilege->wasChanged('can_create_employes') && ! $privilege->can_create_employes) {
            $manager = User::find($privilege->user_id);

            if (! $manager) {
                return;
            }

            $superviseurIds = $manager->superviseurs()->pluck('id');

            UserPrivilege::whereIn('user_id', $superviseurIds)
                ->update(['can_create_employes' => false]);
        }
    }
}
