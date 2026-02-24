<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FormController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $forms = Form::with('creator')
            ->where('company_id', $user->company_id)
            ->when(
                $user->hasRole('manager'),
                fn ($q) => $q->where('created_by', $user->id)
            )
            ->latest()
            ->paginate(15);

        $superviseurIds = \App\Models\User::where('manager_id', $user->id)->pluck('id');

        $branchUsers = \App\Models\User::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($user, $superviseurIds) {
                $q->where('manager_id', $user->id)
                    ->orWhereIn('supervisor_id', $superviseurIds);
            })
            ->orderBy('role')
            ->orderBy('lastname')
            ->get();

        return view('forms.index', compact('forms', 'branchUsers'));
    }

    public function create(): View
    {
        return view('forms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'report_type' => ['required', 'in:type1,type2'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.fields' => ['required', 'array', 'min:1'],
            'sections.*.fields.*.type' => ['required', 'in:text,textarea,select,radio,checkbox,date,number,file,rating,signature'],
            'sections.*.fields.*.label' => ['required', 'string', 'max:255'],
        ]);

        $form = Form::create([
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'report_type' => $validated['report_type'],
            'is_active' => true,
        ]);

        foreach ($validated['sections'] as $sectionIndex => $sectionData) {
            $section = $form->sections()->create([
                'title' => $sectionData['title'],
                'order' => $sectionIndex,
            ]);

            foreach ($sectionData['fields'] as $fieldIndex => $fieldData) {
                $section->fields()->create([
                    'type' => $fieldData['type'],
                    'label' => $fieldData['label'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'is_required' => isset($fieldData['is_required']),
                    'order' => $fieldIndex,
                    'config' => $fieldData['config'] ?? null,
                ]);
            }
        }

        return redirect()->route('forms.show', $form)
            ->with('success', 'Questionnaire créé avec succès.');
    }

    public function show(Form $form): View
    {
        $form->load(['sections.fields', 'creator']);

        return view('forms.show', compact('form'));
    }

    public function edit(Form $form): View
    {
        $form->load('sections.fields');

        return view('forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $form->update($validated);

        return redirect()->route('forms.show', $form)
            ->with('success', 'Questionnaire mis à jour.');
    }

    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Questionnaire supprimé.');
    }

    public function addSection(Request $request, Form $form): Response
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $order = $form->sections()->max('order') + 1;

        $section = $form->sections()->create([
            'title' => $validated['title'],
            'order' => $order,
        ]);

        return response()->view('forms.partials.section', compact('section', 'form'));
    }

    public function addField(Request $request, FormSection $section): Response
    {
        $validated = $request->validate([
            'type' => ['required', 'in:text,textarea,select,radio,checkbox,date,number,file,rating,signature'],
        ]);

        $order = $section->fields()->max('order') + 1;

        $field = $section->fields()->create([
            'type' => $validated['type'],
            'label' => 'Nouveau champ',
            'order' => $order,
        ]);

        $field->load('section');

        return response()->view('forms.partials.field', compact('field'));
    }

    public function updateField(Request $request, FormField $field): Response
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'is_required' => ['boolean'],
            'config' => ['nullable', 'array'],
        ]);

        $field->update($validated);

        return response()->noContent();
    }

    public function deleteField(FormField $field): Response
    {
        $field->delete();

        return response()->noContent();
    }

    public function moveField(Request $request, FormField $field): Response
    {
        $direction = $request->input('direction');
        $fields = $field->section->fields()->orderBy('order')->get();
        $index = $fields->search(fn ($f) => $f->id === $field->id);

        if ($direction === 'up' && $index > 0) {
            $previous = $fields[$index - 1];
            $field->update(['order' => $previous->order]);
            $previous->update(['order' => $field->order]);
        } elseif ($direction === 'down' && $index < $fields->count() - 1) {
            $next = $fields[$index + 1];
            $field->update(['order' => $next->order]);
            $next->update(['order' => $field->order]);
        }

        return response()->noContent();
    }
}
