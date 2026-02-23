<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));

        $employees = $user->hasRole('super-admin') || $user->hasRole('manager')
            ? User::where('company_id', $user->company_id)
                ->when($user->hasRole('manager'), fn ($q) => $q->where('manager_id', $user->id))
                ->get()
            : collect([$user]);

        $employeeId = $request->input('employee_id', $user->hasRole('employee') ? $user->id : null);

        $attendances = Attendance::where('company_id', $user->company_id)
            ->when($employeeId, fn ($q) => $q->where('user_id', $employeeId))
            ->whereBetween('date', [
                now()->parse($month.'-01'),
                now()->parse($month.'-01')->endOfMonth(),
            ])
            ->with('employee', 'enteredBy')
            ->get()
            ->keyBy(fn ($a) => $a->date->format('Y-m-d'));

        return view('attendances.index', compact('attendances', 'employees', 'month', 'employeeId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        Attendance::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date' => $validated['date'],
            ],
            [
                'company_id' => $user->company_id,
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'note' => $validated['note'] ?? null,
                'entered_by' => $user->id,
            ]
        );

        return back()->with('success', 'Présence enregistrée.');
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $attendance->update([
            ...$validated,
            'entered_by' => auth()->id(),
        ]);

        return back()->with('success', 'Présence mise à jour.');
    }
}
