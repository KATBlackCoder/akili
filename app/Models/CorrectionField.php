<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionField extends Model
{
    protected $fillable = [
        'correction_id',
        'field_id',
        'section_id',
    ];

    public function correction(): BelongsTo
    {
        return $this->belongsTo(SubmissionCorrection::class, 'correction_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }
}
