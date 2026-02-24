<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyUrgentReport;
use App\Models\Answer;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportType2Controller extends Controller
{
    public function show(Form $form): View
    {
        abort_unless($form->report_type === 'type2', 404);

        $user = auth()->user();

        $formAssignment = FormAssignment::where('form_id', $form->id)
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where('scope_type', 'individual')
                        ->whereHas('selectedUsers', fn ($u) => $u->where('users.id', $user->id));
                })->orWhere(function ($inner) use ($user) {
                    $inner->where('scope_type', 'role')
                        ->where(function ($r) use ($user) {
                            $r->where('scope_role', $user->role)
                                ->orWhere('scope_role', 'both');
                        });
                });
            })
            ->first();

        abort_unless($formAssignment, 403);

        $form->load('sections.fields');

        return view('reports.type2.show', compact('form', 'formAssignment'));
    }

    public function submit(Form $form, Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'form_assignment_id' => ['required', 'exists:form_assignments,id'],
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'string'],
        ]);

        $submission = Submission::create([
            'company_id' => $user->company_id,
            'form_id' => $form->id,
            'form_assignment_id' => $validated['form_assignment_id'],
            'submitted_by' => $user->id,
            'report_type' => 'type2',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        foreach ($validated['answers'] as $fieldId => $value) {
            Answer::create([
                'submission_id' => $submission->id,
                'field_id' => (int) $fieldId,
                'value' => $value,
            ]);
        }

        NotifyUrgentReport::dispatch($submission)->onQueue('urgent');

        return redirect()->back()->with('success', 'Rapport urgent soumis. Les responsables ont été notifiés.');
    }
}
