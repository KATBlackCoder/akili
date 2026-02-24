<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'form_id',
        'assigned_by',
        'scope_type',
        'scope_role',
        'due_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'is_active' => 'boolean',
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** Utilisateurs sélectionnés individuellement (pivot) */
    public function selectedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_assignment_users')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(SubmissionDraft::class);
    }

    /** Résoudre tous les destinataires effectifs */
    public function resolveRecipients(): Collection
    {
        if ($this->scope_type === 'individual') {
            return $this->selectedUsers;
        }

        $manager = User::find($this->assigned_by);
        $superviseurIds = User::where('manager_id', $manager->id)->pluck('id');

        $recipients = collect();

        if (in_array($this->scope_role, ['superviseur', 'both'])) {
            $recipients = $recipients->merge(
                User::whereIn('id', $superviseurIds)
                    ->where('is_active', true)->get()
            );
        }

        if (in_array($this->scope_role, ['employe', 'both'])) {
            $recipients = $recipients->merge(
                User::whereIn('supervisor_id', $superviseurIds)
                    ->where('is_active', true)->get()
            );
        }

        return $recipients->unique('id');
    }
}
