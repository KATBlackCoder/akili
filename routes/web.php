<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Questionnaires
    Route::resource('forms', FormController::class);
    Route::get('/forms/{form}/export/pdf', [ExportController::class, 'pdf'])->name('forms.export.pdf');
    Route::get('/forms/{form}/export/excel', [ExportController::class, 'excel'])->name('forms.export.excel');

    // Assignations & soumissions
    Route::resource('assignments', AssignmentController::class)->only(['index', 'store', 'destroy']);
    Route::get('/assignments/{assignment}/fill', [SubmissionController::class, 'show'])->name('assignments.fill');
    Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('assignments.submit');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'detail'])->name('submissions.show');

    // Corrections
    Route::post('/submissions/{submission}/return', [CorrectionController::class, 'store'])->name('submissions.return');
    Route::get('/submissions/{submission}/correct', [CorrectionController::class, 'show'])->name('submissions.correct');
    Route::post('/submissions/{submission}/correct', [CorrectionController::class, 'update'])->name('submissions.correct.update');

    // GRH — Employés
    Route::resource('employees', EmployeeController::class)->parameters(['employees' => 'user']);
    Route::patch('/employees/{user}/toggle', [EmployeeController::class, 'toggle'])->name('employees.toggle');

    // Congés
    Route::get('/leave-requests', [LeaveController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests', [LeaveController::class, 'store'])->name('leave-requests.store');
    Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave-requests.approve');
    Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave-requests.reject');

    // Présences
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');

    // Privilèges Manager
    Route::get('/managers/{user}/privileges', [PrivilegeController::class, 'edit'])->name('managers.privileges.edit');
    Route::put('/managers/{user}/privileges', [PrivilegeController::class, 'update'])->name('managers.privileges.update');
    Route::get('/managers/{user}/delegate', [PrivilegeController::class, 'delegateForm'])->name('managers.delegate.form');
    Route::post('/managers/{user}/delegate', [PrivilegeController::class, 'delegate'])->name('managers.delegate');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';
