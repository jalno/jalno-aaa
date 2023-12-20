<?php

namespace dnj\AAA\Database\Factories;

use dnj\AAA\Models\User;
use dnj\AAA\Models\Username;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Username>
 */
class UsernameFactory extends Factory
{
    protected $model = Username::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'username' => fake()->email(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];
    }

    public function withUser(int|User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user,
        ]);
    }

    public function withUsername(string $username): static
    {
        return $this->state(fn () => [
            'username' => $username,
        ]);
    }

    public function withPassword(string $password, bool $hash = false): static
    {
        return $this->state(fn () => [
            'password' => !$hash ? Hash::make($password) : $password,
        ]);
    }
}
