<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'assigned_to',
        'assigned_by',
        'due_at',
        'status',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function submission(): HasOne
    {
        return $this->hasOne(Submission::class);
    }

    public function isExpired(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast() && $this->status === 'pending';
    }

    public function isDueSoon(): bool
    {
        return $this->due_at !== null && $this->due_at->diffInHours(now()) <= 24 && $this->status === 'pending';
    }
}
