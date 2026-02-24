<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrivilege extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'granted_by',
        'can_create_forms',
        'can_create_superviseurs',
        'can_create_employes',
        'can_delegate',
    ];

    protected function casts(): array
    {
        return [
            'can_create_forms' => 'boolean',
            'can_create_superviseurs' => 'boolean',
            'can_create_employes' => 'boolean',
            'can_delegate' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
