<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
            $employeeIds = $user->hasRole('super-admin')
                ? $user->company->users()->pluck('id')
                : $user->subordinates()->pluck('id');

            $totalAssignments = Assignment::whereIn('assigned_to', $employeeIds)->count();
            $completedAssignments = Assignment::whereIn('assigned_to', $employeeIds)->where('status', 'completed')->count();
            $completionRate = $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100) : 0;

            $pendingSubmissions = Submission::whereHas('assignment', fn ($q) => $q->whereIn('assigned_to', $employeeIds))
                ->where('status', 'submitted')
                ->count();

            $activeForms = Form::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->count();

            $recentSubmissions = Submission::with(['assignment.form', 'submitter'])
                ->whereHas('assignment', fn ($q) => $q->whereIn('assigned_to', $employeeIds))
                ->latest()
                ->limit(10)
                ->get();

            $recentAssignments = Assignment::with(['form', 'employee'])
                ->whereIn('assigned_to', $employeeIds)
                ->whereIn('status', ['pending', 'in_progress'])
                ->latest()
                ->limit(6)
                ->get();

            return view('dashboard', compact(
                'completionRate',
                'totalAssignments',
                'completedAssignments',
                'pendingSubmissions',
                'activeForms',
                'recentSubmissions',
                'recentAssignments',
                'employeeIds',
            ));
        }

        // Employé terrain
        $myAssignments = Assignment::with('form')
            ->where('assigned_to', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard-employee', compact('myAssignments'));
    }
}
