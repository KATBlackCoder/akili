<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'created_by' => User::factory()->manager(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'report_type' => fake()->randomElement(['type1', 'type2']),
            'is_active' => true,
        ];
    }

    public function type1(): static
    {
        return $this->state(fn (array $attributes) => ['report_type' => 'type1']);
    }

    public function type2(): static
    {
        return $this->state(fn (array $attributes) => ['report_type' => 'type2']);
    }
}
