<?php

use App\Models\Company;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\SubmissionDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->manager = User::factory()->manager()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    $this->employe = User::factory()->employe()->create(['company_id' => $this->company->id]);
    $this->employe->assignRole('employe');

    $this->form = Form::factory()->create([
        'company_id' => $this->company->id,
        'created_by' => $this->manager->id,
        'report_type' => 'type1',
    ]);

    $section = FormSection::factory()->create(['form_id' => $this->form->id]);
    FormField::factory()->create(['section_id' => $section->id, 'type' => 'text', 'label' => 'Activité']);

    $this->assignment = FormAssignment::factory()->individual([$this->employe->id])->create([
        'company_id' => $this->company->id,
        'form_id' => $this->form->id,
        'assigned_by' => $this->manager->id,
        'is_active' => true,
    ]);
});

it('employe can view type1 report page', function () {
    $this->actingAs($this->employe)
        ->get(route('reports.type1.show', $this->form))
        ->assertSuccessful();
});

it('saves a draft server-side', function () {
    $this->actingAs($this->employe)
        ->post(route('reports.type1.draft', $this->form), [
            'form_assignment_id' => $this->assignment->id,
            'draft_data' => ['1' => ['field_1' => 'test value']],
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('submission_drafts', [
        'form_id' => $this->form->id,
        'user_id' => $this->employe->id,
        'form_assignment_id' => $this->assignment->id,
    ]);
});

it('submits type1 report and removes draft', function () {
    SubmissionDraft::create([
        'company_id' => $this->company->id,
        'form_id' => $this->form->id,
        'user_id' => $this->employe->id,
        'form_assignment_id' => $this->assignment->id,
        'draft_data' => [],
        'last_synced_at' => now(),
    ]);

    $field = $this->form->fields()->first();

    $this->actingAs($this->employe)
        ->post(route('reports.type1.submit', $this->form), [
            'form_assignment_id' => $this->assignment->id,
            'rows' => [
                1 => [$field->id => 'Activité du matin'],
            ],
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('submissions', [
        'form_id' => $this->form->id,
        'submitted_by' => $this->employe->id,
        'report_type' => 'type1',
        'status' => 'submitted',
    ]);

    $this->assertDatabaseMissing('submission_drafts', [
        'form_id' => $this->form->id,
        'user_id' => $this->employe->id,
    ]);
});

it('unauthorized user cannot access type1 report', function () {
    $otherUser = User::factory()->employe()->create(['company_id' => $this->company->id]);
    $otherUser->assignRole('employe');

    $this->actingAs($otherUser)
        ->get(route('reports.type1.show', $this->form))
        ->assertForbidden();
});
