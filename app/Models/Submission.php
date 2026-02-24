<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'company_id',
        'form_id',
        'assignment_id',
        'form_assignment_id',
        'submitted_by',
        'report_type',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function formAssignment(): BelongsTo
    {
        return $this->belongsTo(FormAssignment::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SubmissionRow::class)->orderBy('row_index');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(SubmissionCorrection::class);
    }

    public function activeCorrection(): HasMany
    {
        return $this->hasMany(SubmissionCorrection::class)->where('status', 'pending')->latest();
    }
}
