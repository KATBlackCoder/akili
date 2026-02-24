<?php

namespace Database\Factories;

use App\Models\FormSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => FormSection::factory(),
            'type' => fake()->randomElement(['text', 'textarea', 'number', 'date']),
            'label' => fake()->words(2, true),
            'placeholder' => null,
            'is_required' => false,
            'order' => 0,
            'config' => null,
        ];
    }
}
