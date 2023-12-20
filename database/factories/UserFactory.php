<?php

namespace dnj\AAA\Database\Factories;

use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Models\Type;
use dnj\AAA\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => fake()->words(2, true),
            'type_id' => Type::factory(),
            'meta' => ['key' => 'value'],
            'status' => UserStatus::ACTIVE,
            'ping_at' => null,
        ];
    }

    public function withName(string $name): static
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }

    public function withType(int|Type $type): static
    {
        return $this->state(fn () => [
            'type_id' => $type,
        ]);
    }

    public function withStatus(UserStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }

    /**
     * @param array<mixed,mixed> $meta
     */
    public function withMeta(array $meta): static
    {
        return $this->state(fn () => [
            'meta' => $meta,
        ]);
    }

    public function isOnline(): static
    {
        return $this->state(fn () => [
            'ping_at' => now(),
        ]);
    }
}
