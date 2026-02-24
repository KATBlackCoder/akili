<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Form;
use App\Models\User;
use App\Notifications\FormAssigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $assignments = Assignment::with(['form', 'employee', 'submission'])
            ->where(function ($query) use ($user) {
                if ($user->hasRole('super-admin')) {
                    $query->whereHas('form', fn ($q) => $q->where('company_id', $user->company_id));
                } elseif ($user->hasRole('manager')) {
                    $query->where('assigned_by', $user->id);
                } else {
                    $query->where('assigned_to', $user->id);
                }
            })
            ->latest()
            ->paginate(15);

        $forms = Form::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();

        $employees = $user->hasRole('super-admin')
            ? User::where('company_id', $user->company_id)->role('employe')->get()
            : $user->subordinates()->get();

        return view('assignments.index', compact('assignments', 'forms', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'form_id' => ['required', 'exists:forms,id'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:users,id'],
            'due_at' => ['nullable', 'date', 'after:now'],
        ]);

        foreach ($validated['employee_ids'] as $employeeId) {
            $assignment = Assignment::create([
                'form_id' => $validated['form_id'],
                'assigned_to' => $employeeId,
                'assigned_by' => $request->user()->id,
                'due_at' => $validated['due_at'] ?? null,
                'status' => 'pending',
            ]);

            $employee = User::find($employeeId);
            $employee->notify(new FormAssigned($assignment));
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Questionnaire assigné avec succès.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Assignation supprimée.');
    }
}
