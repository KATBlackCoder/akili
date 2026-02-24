<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $employees = User::with(['supervisor', 'manager'])
            ->where('company_id', $user->company_id)
            ->when(
                $user->hasRole('manager'),
                fn ($q) => $q->where(function ($inner) use ($user) {
                    $inner->where('manager_id', $user->id)
                        ->orWhereIn('supervisor_id', $user->superviseurs()->pluck('id'));
                })
            )
            ->when(
                $user->hasRole('superviseur'),
                fn ($q) => $q->where('supervisor_id', $user->id)
            )
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(function ($inner) use ($request) {
                    $inner->where('firstname', 'like', '%'.$request->search.'%')
                        ->orWhere('lastname', 'like', '%'.$request->search.'%')
                        ->orWhere('username', 'like', '%'.$request->search.'%');
                })
            )
            ->latest()
            ->paginate(15);

        if ($request->header('HX-Request')) {
            return view('employees.partials.table', compact('employees'));
        }

        return view('employees.index', compact('employees'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('users.create');
    }

    public function show(User $user): View
    {
        $user->load(['supervisor', 'manager', 'assignments.form', 'leaveRequests', 'privilege']);

        return view('employees.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $managers = User::where('company_id', $user->company_id)->where('role', 'manager')->get();
        $superviseurs = User::where('company_id', $user->company_id)->where('role', 'superviseur')->get();

        return view('employees.edit', compact('user', 'managers', 'superviseurs'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'hired_at' => ['nullable', 'date'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'local');
        }

        unset($validated['avatar']);
        $user->update($validated);

        return redirect()->route('employees.show', $user)
            ->with('success', 'Profil mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 403, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function toggle(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Compte {$status}.");
    }
}
