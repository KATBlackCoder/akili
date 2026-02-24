<?php

use App\Jobs\NotifyFormAssigned;
use App\Models\Company;
use App\Models\Form;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();

    $this->manager = User::factory()->manager()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    UserPrivilege::create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'granted_by' => $this->manager->id,
        'can_create_forms' => true,
    ]);

    $this->form = Form::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->manager->id,
        'report_type' => 'type1',
    ]);
});

it('manager can assign a form by role to employes', function () {
    Queue::fake();

    $this->actingAs($this->manager)
        ->post(route('forms.assign', $this->form), [
            'scope_type' => 'role',
            'scope_role' => 'employe',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('form_assignments', [
        'form_id' => $this->form->id,
        'scope_type' => 'role',
        'scope_role' => 'employe',
        'assigned_by' => $this->manager->id,
    ]);

    Queue::assertPushedOn('default', NotifyFormAssigned::class);
});

it('manager can assign a form with scope_role both to superviseurs and employes', function () {
    Queue::fake();

    $this->actingAs($this->manager)
        ->post(route('forms.assign', $this->form), [
            'scope_type' => 'role',
            'scope_role' => 'both',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('form_assignments', [
        'form_id' => $this->form->id,
        'scope_type' => 'role',
        'scope_role' => 'both',
    ]);

    Queue::assertPushedOn('default', NotifyFormAssigned::class);
});

it('manager can assign a form to multiple individual users', function () {
    Queue::fake();

    $superviseur = User::factory()->superviseur()->create([
        'company_id' => $this->company->id,
        'manager_id' => $this->manager->id,
    ]);
    $superviseur->assignRole('superviseur');

    $employe = User::factory()->employe()->create([
        'company_id' => $this->company->id,
        'supervisor_id' => $superviseur->id,
    ]);
    $employe->assignRole('employe');

    $this->actingAs($this->manager)
        ->post(route('forms.assign', $this->form), [
            'scope_type' => 'individual',
            'user_ids' => [$superviseur->id, $employe->id],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('form_assignments', [
        'form_id' => $this->form->id,
        'scope_type' => 'individual',
    ]);

    $assignment = \App\Models\FormAssignment::where('form_id', $this->form->id)->first();
    expect($assignment->selectedUsers()->count())->toBe(2);

    Queue::assertPushedOn('default', NotifyFormAssigned::class);
});

it('rejects individual assignment with users outside the branch', function () {
    $outsider = User::factory()->employe()->create(['company_id' => $this->company->id]);
    $outsider->assignRole('employe');

    $this->actingAs($this->manager)
        ->post(route('forms.assign', $this->form), [
            'scope_type' => 'individual',
            'user_ids' => [$outsider->id],
        ])
        ->assertForbidden();
});

it('employe without privilege cannot assign forms', function () {
    $employe = User::factory()->employe()->create(['company_id' => $this->company->id]);
    $employe->assignRole('employe');

    $this->actingAs($employe)
        ->post(route('forms.assign', $this->form), [
            'scope_type' => 'role',
            'scope_role' => 'employe',
        ])
        ->assertForbidden();
});
