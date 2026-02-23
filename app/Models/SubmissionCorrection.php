<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionCorrection extends Model
{
    protected $fillable = [
        'submission_id',
        'requested_by',
        'message',
        'scope',
        'status',
        'corrected_at',
    ];

    protected function casts(): array
    {
        return [
            'corrected_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function correctionFields(): HasMany
    {
        return $this->hasMany(CorrectionField::class, 'correction_id');
    }
}
