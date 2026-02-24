<?php

use App\Jobs\NotifyUrgentReport;
use App\Models\Company;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

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
        'report_type' => 'type2',
    ]);

    $section = FormSection::factory()->create(['form_id' => $this->form->id]);
    FormField::factory()->create(['section_id' => $section->id, 'type' => 'textarea', 'label' => 'Description']);

    $this->assignment = FormAssignment::factory()->individual([$this->employe->id])->create([
        'company_id' => $this->company->id,
        'form_id' => $this->form->id,
        'assigned_by' => $this->manager->id,
        'is_active' => true,
    ]);
});

it('submits multiple type2 reports independently', function () {
    Queue::fake();

    $field = $this->form->fields()->first();

    $this->actingAs($this->employe)
        ->post(route('reports.type2.submit', $this->form), [
            'form_assignment_id' => $this->assignment->id,
            'answers' => [$field->id => 'Incident 1'],
        ])
        ->assertRedirect();

    $this->actingAs($this->employe)
        ->post(route('reports.type2.submit', $this->form), [
            'form_assignment_id' => $this->assignment->id,
            'answers' => [$field->id => 'Incident 2'],
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('submissions', 2);

    Queue::assertPushed(NotifyUrgentReport::class, 2);
});

it('dispatches NotifyUrgentReport on urgent queue', function () {
    Queue::fake();

    $field = $this->form->fields()->first();

    $this->actingAs($this->employe)
        ->post(route('reports.type2.submit', $this->form), [
            'form_assignment_id' => $this->assignment->id,
            'answers' => [$field->id => 'Urgence grave'],
        ]);

    Queue::assertPushedOn('urgent', NotifyUrgentReport::class);
});
