<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
});

it('super admin can create a manager with privileges', function () {
    $superAdmin = User::factory()->superAdmin()->create(['company_id' => $this->company->id]);
    $superAdmin->assignRole('super-admin');

    $this->actingAs($superAdmin)
        ->post(route('users.store'), [
            'role' => 'manager',
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'phone' => '0611223344',
            'privileges' => ['can_create_forms', 'can_create_superviseurs'],
        ])
        ->assertRedirect(route('employees.index'));

    $this->assertDatabaseHas('users', [
        'firstname' => 'Jean',
        'lastname' => 'Dupont',
        'role' => 'manager',
        'company_id' => $this->company->id,
    ]);

    $manager = User::where('lastname', 'Dupont')->first();
    $this->assertDatabaseHas('user_privileges', [
        'user_id' => $manager->id,
        'can_create_forms' => true,
        'can_create_superviseurs' => true,
    ]);
});

it('privileged manager can create a superviseur with manager_id', function () {
    $manager = User::factory()->manager()->create(['company_id' => $this->company->id]);
    $manager->assignRole('manager');

    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $manager->id,
        'granted_by' => $manager->id,
        'can_create_superviseurs' => true,
        'can_create_employes' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role' => 'superviseur',
            'firstname' => 'Marie',
            'lastname' => 'Martin',
            'phone' => '0622334455',
            'manager_id' => $manager->id,
        ])
        ->assertRedirect(route('employees.index'));

    $this->assertDatabaseHas('users', [
        'firstname' => 'Marie',
        'role' => 'superviseur',
        'manager_id' => $manager->id,
    ]);
});

it('privileged superviseur can create an employe', function () {
    $superviseur = User::factory()->superviseur()->create(['company_id' => $this->company->id]);
    $superviseur->assignRole('superviseur');

    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $superviseur->id,
        'granted_by' => $superviseur->id,
        'can_create_employes' => true,
    ]);

    $this->actingAs($superviseur)
        ->post(route('users.store'), [
            'role' => 'employe',
            'firstname' => 'Pierre',
            'lastname' => 'Durand',
            'phone' => '0633445566',
            'supervisor_id' => $superviseur->id,
        ])
        ->assertRedirect(route('employees.index'));

    $this->assertDatabaseHas('users', [
        'firstname' => 'Pierre',
        'role' => 'employe',
        'supervisor_id' => $superviseur->id,
    ]);
});

it('manager cannot create another manager', function () {
    $manager = User::factory()->manager()->create(['company_id' => $this->company->id]);
    $manager->assignRole('manager');

    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $manager->id,
        'granted_by' => $manager->id,
        'can_create_superviseurs' => true,
        'can_create_employes' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role' => 'manager',
            'firstname' => 'Bob',
            'lastname' => 'Test',
            'phone' => '0644556677',
        ])
        ->assertForbidden();
});

it('enforces visibility: superviseur only sees their employes', function () {
    $superviseur = User::factory()->superviseur()->create(['company_id' => $this->company->id]);
    $superviseur->assignRole('superviseur');

    $myEmployee = User::factory()->employe()->create([
        'company_id' => $this->company->id,
        'supervisor_id' => $superviseur->id,
    ]);

    $otherEmployee = User::factory()->employe()->create([
        'company_id' => $this->company->id,
    ]);

    $response = $this->actingAs($superviseur)->get(route('employees.index'));

    $response->assertSuccessful();
    $response->assertSee($myEmployee->full_name);
    $response->assertDontSee($otherEmployee->full_name);
});
