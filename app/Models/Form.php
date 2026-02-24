<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'description',
        'report_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('order');
    }

    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(
            FormField::class,
            FormSection::class,
            'form_id',
            'section_id'
        );
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function formAssignments(): HasMany
    {
        return $this->hasMany(FormAssignment::class);
    }

    public function isType1(): bool
    {
        return $this->report_type === 'type1';
    }

    public function isType2(): bool
    {
        return $this->report_type === 'type2';
    }
}
