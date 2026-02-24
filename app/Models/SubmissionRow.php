<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionRow extends Model
{
    protected $fillable = [
        'submission_id',
        'row_index',
    ];

    protected function casts(): array
    {
        return [
            'row_index' => 'integer',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'row_id');
    }
}
