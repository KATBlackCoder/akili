<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function show(Assignment $assignment): View
    {
        abort_if($assignment->assigned_to !== auth()->id(), 403);
        abort_if($assignment->status === 'completed', 403, 'Ce questionnaire a déjà été soumis.');

        $assignment->load(['form.sections.fields']);

        return view('submissions.fill', compact('assignment'));
    }

    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_if($assignment->assigned_to !== auth()->id(), 403);
        abort_if($assignment->status === 'completed', 403);

        $assignment->load(['form.sections.fields']);

        $rules = [];
        foreach ($assignment->form->sections as $section) {
            foreach ($section->fields as $field) {
                $fieldRules = [];
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                if ($field->type === 'file') {
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240';
                } elseif ($field->type === 'number' || $field->type === 'rating') {
                    $fieldRules[] = 'numeric';
                } elseif ($field->type === 'date') {
                    $fieldRules[] = 'date';
                }

                $rules["fields.{$field->id}"] = $fieldRules;
            }
        }

        $validated = $request->validate($rules);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'submitted_by' => auth()->id(),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        foreach ($assignment->form->sections as $section) {
            foreach ($section->fields as $field) {
                $value = $validated["fields.{$field->id}"] ?? null;
                $filePath = null;

                if ($field->type === 'file' && $request->hasFile("fields.{$field->id}")) {
                    $filePath = $request->file("fields.{$field->id}")->store('submissions', 'local');
                    $value = null;
                } elseif (is_array($value)) {
                    $value = json_encode($value);
                }

                $submission->answers()->create([
                    'field_id' => $field->id,
                    'value' => $value,
                    'file_path' => $filePath,
                ]);
            }
        }

        $assignment->update(['status' => 'completed']);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Questionnaire soumis avec succès.');
    }

    public function detail(Submission $submission): View
    {
        $user = auth()->user();

        abort_if(
            ! $user->hasRole('super-admin') &&
            ! $user->hasRole('manager') &&
            $submission->submitted_by !== $user->id,
            403
        );

        $submission->load([
            'assignment.form.sections.fields',
            'answers',
            'corrections.correctionFields',
            'corrections.requestedBy',
        ]);

        return view('submissions.show', compact('submission'));
    }
}
