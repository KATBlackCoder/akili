<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'firstname',
        'lastname',
        'username',
        'password',
        'must_change_password',
        'role',
        'manager_id',
        'supervisor_id',
        'phone',
        'avatar_path',
        'hired_at',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'hired_at' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Superviseur → son Manager */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /** Employé → son Superviseur */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /** Manager → ses Superviseurs directs */
    public function superviseurs(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id')
            ->where('role', 'superviseur');
    }

    /** Superviseur → ses Employés directs */
    public function employes(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id')
            ->where('role', 'employe');
    }

    public function privilege(): HasOne
    {
        return $this->hasOne(UserPrivilege::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_to');
    }

    public function assignedForms(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'submitted_by');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'created_by');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function canCreateForms(): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->privilege?->can_create_forms ?? false;
    }

    public function canCreateSuperviseurs(): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->privilege?->can_create_superviseurs ?? false;
    }

    public function canCreateEmployes(): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->privilege?->can_create_employes ?? false;
    }

    public function canDelegate(): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->privilege?->can_delegate ?? false;
    }
}
