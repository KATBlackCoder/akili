<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\Submission;
use App\Models\SubmissionDraft;
use App\Models\SubmissionRow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportType1Controller extends Controller
{
    public function show(Form $form): View
    {
        abort_unless($form->report_type === 'type1', 404);

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

        $draft = SubmissionDraft::where('form_id', $form->id)
            ->where('user_id', $user->id)
            ->where('form_assignment_id', $formAssignment->id)
            ->first();

        $form->load('sections.fields');

        return view('reports.type1.show', compact('form', 'formAssignment', 'draft'));
    }

    public function addRow(Form $form, Request $request): View
    {
        $rowIndex = (int) $request->input('current_row_count', 0) + 1;
        $form->load('sections.fields');

        return view('reports.type1.partials.row', [
            'form' => $form,
            'rowIndex' => $rowIndex,
        ]);
    }

    public function deleteRow(Form $form, int $row): \Illuminate\Contracts\View\View|\Illuminate\Http\Response
    {
        return response('', 200);
    }

    public function saveDraft(Form $form, Request $request): View
    {
        $user = auth()->user();

        SubmissionDraft::updateOrCreate(
            [
                'form_id' => $form->id,
                'user_id' => $user->id,
                'form_assignment_id' => $request->input('form_assignment_id'),
            ],
            [
                'company_id' => $user->company_id,
                'draft_data' => $request->input('draft_data', []),
                'last_synced_at' => now(),
            ]
        );

        return view('reports.type1.partials.draft-badge', ['time' => now()->format('H:i')]);
    }

    public function submit(Form $form, Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'form_assignment_id' => ['required', 'exists:form_assignments,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*' => ['array'],
        ]);

        $submission = Submission::create([
            'company_id' => $user->company_id,
            'form_id' => $form->id,
            'form_assignment_id' => $validated['form_assignment_id'],
            'submitted_by' => $user->id,
            'report_type' => 'type1',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        foreach ($validated['rows'] as $rowIndex => $fieldAnswers) {
            $row = SubmissionRow::create([
                'submission_id' => $submission->id,
                'row_index' => (int) $rowIndex,
            ]);

            foreach ($fieldAnswers as $fieldId => $value) {
                Answer::create([
                    'submission_id' => $submission->id,
                    'row_id' => $row->id,
                    'field_id' => (int) $fieldId,
                    'value' => $value,
                ]);
            }
        }

        SubmissionDraft::where('form_id', $form->id)
            ->where('user_id', $user->id)
            ->where('form_assignment_id', $validated['form_assignment_id'])
            ->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Rapport journalier soumis avec succès.');
    }
}
