<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionDraft extends Model
{
    protected $fillable = [
        'company_id',
        'form_id',
        'user_id',
        'form_assignment_id',
        'draft_data',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'last_synced_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formAssignment(): BelongsTo
    {
        return $this->belongsTo(FormAssignment::class);
    }
}
