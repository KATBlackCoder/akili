<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();

    $this->manager = User::factory()->manager()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    $this->superviseur = User::factory()->superviseur()->create([
        'company_id' => $this->company->id,
        'manager_id' => $this->manager->id,
    ]);
    $this->superviseur->assignRole('superviseur');
});

it('manager can delegate can_create_employes to their superviseur', function () {
    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'granted_by' => $this->manager->id,
        'can_create_employes' => true,
        'can_delegate' => true,
    ]);

    $this->actingAs($this->manager)
        ->post(route('managers.delegate', $this->superviseur), [
            'can_create_employes' => true,
        ])
        ->assertRedirect(route('employees.show', $this->superviseur));

    $this->assertDatabaseHas('user_privileges', [
        'user_id' => $this->superviseur->id,
        'can_create_employes' => true,
    ]);
});

it('manager cannot delegate can_create_forms to a superviseur', function () {
    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'granted_by' => $this->manager->id,
        'can_create_forms' => true,
        'can_delegate' => true,
    ]);

    $this->actingAs($this->manager)
        ->post(route('managers.delegate', $this->superviseur), [
            'can_create_forms' => true,
        ])
        ->assertRedirect();

    $privilege = UserPrivilege::where('user_id', $this->superviseur->id)->first();
    expect($privilege?->can_create_forms ?? false)->toBeFalse();
});

it('observer revokes can_create_employes in cascade when manager loses it', function () {
    $managerPrivilege = UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'granted_by' => $this->manager->id,
        'can_create_employes' => true,
    ]);

    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->superviseur->id,
        'granted_by' => $this->manager->id,
        'can_create_employes' => true,
    ]);

    $managerPrivilege->update(['can_create_employes' => false]);

    $this->assertDatabaseHas('user_privileges', [
        'user_id' => $this->superviseur->id,
        'can_create_employes' => false,
    ]);
});

it('superviseur cannot delegate anything', function () {
    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->superviseur->id,
        'granted_by' => $this->manager->id,
        'can_create_employes' => true,
        'can_delegate' => false,
    ]);

    $employe = User::factory()->employe()->create(['company_id' => $this->company->id]);
    $employe->assignRole('employe');

    $this->actingAs($this->superviseur)
        ->post(route('managers.delegate', $employe), [
            'can_create_employes' => true,
        ])
        ->assertForbidden();
});
