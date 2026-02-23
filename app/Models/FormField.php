<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    protected $fillable = [
        'section_id',
        'type',
        'label',
        'placeholder',
        'is_required',
        'order',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
            'config' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'field_id');
    }

    public function correctionFields(): HasMany
    {
        return $this->hasMany(CorrectionField::class, 'field_id');
    }
}
