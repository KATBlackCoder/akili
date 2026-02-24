<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstname = fake()->firstName();
        $lastname = fake()->lastName();
        $phone = fake()->numerify('06########');

        return [
            'company_id' => Company::factory(),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => Str::lower($lastname).'@'.$phone.'.org',
            'password' => static::$password ??= Hash::make('password'),
            'must_change_password' => false,
            'role' => 'employe',
            'phone' => $phone,
            'is_active' => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }

    public function superviseur(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'superviseur',
        ]);
    }

    public function employe(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'employe',
        ]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
