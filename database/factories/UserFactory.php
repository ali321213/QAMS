<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => substr($name, 0, 30),
            'user_name' => strtolower(str_replace(' ', '', fake()->unique()->userName())),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'student',
            'active' => '1',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrator',
            'user_name' => 'admin',
            'role' => 'admin',
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher',
        ]);
    }
}
