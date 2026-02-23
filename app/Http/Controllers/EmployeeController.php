<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $employees = User::with('manager')
            ->where('company_id', $user->company_id)
            ->when(
                $user->hasRole('manager'),
                fn ($q) => $q->where('manager_id', $user->id)
            )
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(function ($inner) use ($request) {
                    $inner->where('firstname', 'like', '%'.$request->search.'%')
                        ->orWhere('lastname', 'like', '%'.$request->search.'%')
                        ->orWhere('username', 'like', '%'.$request->search.'%')
                        ->orWhere('department', 'like', '%'.$request->search.'%');
                })
            )
            ->latest()
            ->paginate(15);

        if ($request->header('HX-Request')) {
            return view('employees.partials.table', compact('employees'));
        }

        return view('employees.index', compact('employees'));
    }

    public function create(Request $request): View
    {
        $managers = User::where('company_id', $request->user()->company_id)
            ->role('manager')
            ->get();

        return view('employees.create', compact('managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'hired_at' => ['nullable', 'date'],
        ]);

        $username = Str::lower($validated['lastname']).'@'.$validated['phone'].'.org';

        if (User::where('username', $username)->exists()) {
            return back()->withErrors(['phone' => 'Un utilisateur avec ce nom et ce téléphone existe déjà.'])->withInput();
        }

        $user = User::create([
            'company_id' => $request->user()->company_id,
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'username' => $username,
            'password' => Hash::make('ML'.$validated['phone']),
            'must_change_password' => true,
            'phone' => $validated['phone'],
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'hired_at' => $validated['hired_at'] ?? null,
            'is_active' => true,
        ]);

        $user->assignRole('employee');

        return redirect()->route('employees.show', $user)
            ->with('success', "Employé créé. Identifiants : {$username} / ML{$validated['phone']}");
    }

    public function show(User $user): View
    {
        $user->load(['manager', 'assignments.form', 'leaveRequests', 'privilege']);

        return view('employees.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $managers = User::where('company_id', $user->company_id)->role('manager')->get();

        return view('employees.edit', compact('user', 'managers'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
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
            ->with('success', 'Employé supprimé.');
    }

    public function toggle(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Compte {$status}.");
    }
}
