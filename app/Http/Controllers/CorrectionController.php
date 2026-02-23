<?php

namespace App\Http\Controllers;

use App\Models\CorrectionField;
use App\Models\Submission;
use App\Models\SubmissionCorrection;
use App\Notifications\SubmissionReturned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorrectionController extends Controller
{
    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:partial,full'],
            'message' => ['nullable', 'string', 'max:2000'],
            'field_ids' => ['required_if:scope,partial', 'nullable', 'array'],
            'field_ids.*' => ['exists:form_fields,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['exists:form_sections,id'],
        ]);

        $correction = SubmissionCorrection::create([
            'submission_id' => $submission->id,
            'requested_by' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'scope' => $validated['scope'],
            'status' => 'pending',
        ]);

        if ($validated['scope'] === 'partial') {
            foreach ($validated['field_ids'] ?? [] as $fieldId) {
                CorrectionField::create([
                    'correction_id' => $correction->id,
                    'field_id' => $fieldId,
                ]);
            }

            foreach ($validated['section_ids'] ?? [] as $sectionId) {
                CorrectionField::create([
                    'correction_id' => $correction->id,
                    'section_id' => $sectionId,
                ]);
            }
        }

        $submission->update(['status' => 'returned']);
        $submission->assignment->update(['status' => 'pending']);

        $submission->submitter->notify(new SubmissionReturned($submission, $correction));

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Soumission renvoyée en correction.');
    }

    public function show(Submission $submission): View
    {
        abort_if($submission->submitted_by !== auth()->id(), 403);
        abort_if($submission->status !== 'returned', 403, 'Cette soumission n\'est pas en attente de correction.');

        $submission->load([
            'assignment.form.sections.fields',
            'answers',
        ]);

        $activeCorrection = $submission->corrections()
            ->with('correctionFields')
            ->where('status', 'pending')
            ->latest()
            ->first();

        $lockedFieldIds = [];
        if ($activeCorrection && $activeCorrection->scope === 'partial') {
            $targetedFieldIds = $activeCorrection->correctionFields->whereNotNull('field_id')->pluck('field_id')->toArray();
            $allFieldIds = $submission->assignment->form->sections
                ->flatMap(fn ($s) => $s->fields->pluck('id'))
                ->toArray();
            $lockedFieldIds = array_diff($allFieldIds, $targetedFieldIds);
        }

        return view('submissions.correct', compact('submission', 'activeCorrection', 'lockedFieldIds'));
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        abort_if($submission->submitted_by !== auth()->id(), 403);
        abort_if($submission->status !== 'returned', 403);

        $submission->load(['assignment.form.sections.fields', 'answers']);

        $activeCorrection = $submission->corrections()
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $lockedFieldIds = [];
        if ($activeCorrection->scope === 'partial') {
            $targetedFieldIds = $activeCorrection->correctionFields->whereNotNull('field_id')->pluck('field_id')->toArray();
            $allFieldIds = $submission->assignment->form->sections
                ->flatMap(fn ($s) => $s->fields->pluck('id'))
                ->toArray();
            $lockedFieldIds = array_diff($allFieldIds, $targetedFieldIds);
        }

        foreach ($submission->assignment->form->sections as $section) {
            foreach ($section->fields as $field) {
                if (in_array($field->id, $lockedFieldIds)) {
                    continue;
                }

                $value = $request->input("fields.{$field->id}");
                $filePath = null;

                if ($field->type === 'file' && $request->hasFile("fields.{$field->id}")) {
                    $filePath = $request->file("fields.{$field->id}")->store('submissions', 'local');
                    $value = null;
                } elseif (is_array($value)) {
                    $value = json_encode($value);
                }

                $submission->answers()->updateOrCreate(
                    ['field_id' => $field->id],
                    ['value' => $value, 'file_path' => $filePath]
                );
            }
        }

        $activeCorrection->update([
            'status' => 'corrected',
            'corrected_at' => now(),
        ]);

        $submission->update(['status' => 'corrected']);
        $submission->assignment->update(['status' => 'completed']);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Correction soumise avec succès.');
    }
}
