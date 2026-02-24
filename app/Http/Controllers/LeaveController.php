<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $leaveRequests = LeaveRequest::with('employee', 'manager')
            ->where('company_id', $user->company_id)
            ->when(
                $user->hasRole('manager'),
                fn ($q) => $q->whereIn('user_id', $user->subordinates()->pluck('id'))
            )
            ->when(
                $user->hasRole('employe'),
                fn ($q) => $q->where('user_id', $user->id)
            )
            ->latest()
            ->paginate(15);

        return view('leave-requests.index', compact('leaveRequests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:paid,unpaid,sick,other'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'manager_id' => $user->manager_id ?? $user->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Demande de congé soumise.');
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        $leaveRequest->employee->notify(new LeaveRequestStatusChanged($leaveRequest));

        return back()->with('success', 'Congé approuvé.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'review_comment' => $request->input('comment'),
        ]);

        $leaveRequest->employee->notify(new LeaveRequestStatusChanged($leaveRequest));

        return back()->with('success', 'Congé refusé.');
    }
}
