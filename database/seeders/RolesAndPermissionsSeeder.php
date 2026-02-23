<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('employee', 'web');
    }
}
