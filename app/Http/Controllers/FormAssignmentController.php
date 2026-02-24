<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyFormAssigned;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormAssignmentController extends Controller
{
    public function store(Form $form, Request $request): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user->canCreateForms() || $user->hasRole('super-admin'), 403);

        $validated = $request->validate([
            'scope_type' => ['required', 'in:role,individual'],
            'scope_role' => ['required_if:scope_type,role', 'nullable', 'in:superviseur,employe,both'],
            'user_ids' => ['required_if:scope_type,individual', 'nullable', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'due_at' => ['nullable', 'date', 'after:now'],
        ]);

        $assignment = FormAssignment::create([
            'company_id' => $user->company_id,
            'form_id' => $form->id,
            'assigned_by' => $user->id,
            'scope_type' => $validated['scope_type'],
            'scope_role' => $validated['scope_role'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'is_active' => true,
        ]);

        if ($validated['scope_type'] === 'individual') {
            $this->validateUsersInBranch($validated['user_ids'] ?? []);
            $assignment->selectedUsers()->attach($validated['user_ids']);
        }

        NotifyFormAssigned::dispatch($assignment)->onQueue('default');

        return redirect()->back()->with('success', 'Questionnaire assigné.');
    }

    private function validateUsersInBranch(array $userIds): void
    {
        $manager = auth()->user();
        $superviseurIds = User::where('manager_id', $manager->id)->pluck('id');
        $employeIds = User::whereIn('supervisor_id', $superviseurIds)->pluck('id');
        $authorizedIds = $superviseurIds->merge($employeIds)->toArray();

        if (! empty(array_diff($userIds, $authorizedIds))) {
            abort(403, 'Certains utilisateurs ne sont pas dans votre branche.');
        }
    }
}
