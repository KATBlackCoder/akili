<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormAssignment>
 */
class FormAssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'form_id' => Form::factory(),
            'assigned_by' => User::factory()->manager(),
            'scope_type' => 'role',
            'scope_role' => 'employe',
            'due_at' => null,
            'is_active' => true,
        ];
    }

    /** @param array<int> $userIds */
    public function individual(array $userIds = []): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => 'individual',
            'scope_role' => null,
        ])->afterCreating(function ($assignment) use ($userIds) {
            if (! empty($userIds)) {
                $assignment->selectedUsers()->attach($userIds);
            }
        });
    }
}
