<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Mon Entreprise',
                'timezone' => 'Africa/Douala',
            ]
        );

        $admin = User::firstOrCreate(
            ['username' => 'admin@akili.local'],
            [
                'company_id' => $company->id,
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'username' => 'admin@akili.local',
                'password' => Hash::make('password'),
                'must_change_password' => false,
                'role' => 'super_admin',
                'phone' => '0000000000',
                'is_active' => true,
            ]
        );

        $admin->assignRole('super-admin');

        $this->command->info('Super Admin créé : admin@akili.local / password');
    }
}
