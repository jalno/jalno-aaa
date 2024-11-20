<?php

namespace Jalno\AAA\Database\Factories;

use dnj\AAA\Contracts\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Jalno\AAA\Models\Country;
use Jalno\AAA\Models\Type;
use Jalno\AAA\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->unique()->email(),
            'cellphone' => fake()->countryCode().'.'.fake()->phoneNumber(),
            'password' => static::$password ??= password_hash('password', PASSWORD_DEFAULT),
            'type' => Type::factory(),
            'phone' => fake()->countryCode().'.'.fake()->phoneNumber(),
            'city' => fake()->city(),
            'country' => Country::factory(),
            'zip' => fake()->postcode(),
            'address' => fake()->streetAddress(),
            'web' => fake()->url(),
            'registered_at' => fake()->unixTime(time()),
            'lastonline' => fake()->unixTime(time()),
            'remember_token' => null,
            'credit' => 0,
            'avatar' => null,
            'has_custom_permissions' => 0,
            'status' => UserStatus::ACTIVE,
        ];
    }

    public function withName(string $name, ?string $lastname): static
    {
        return $this->state(fn () => [
            'name' => $name,
            'lastname' => $lastname,
        ]);
    }

    public function withEmail(string $email): static
    {
        return $this->state(fn () => [
            'email' => $email,
        ]);
    }

    public function withCellphone(string $cellphone): static
    {
        return $this->state(fn () => [
            'cellphone' => $cellphone,
        ]);
    }

    public function withType(int|Type $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
        ]);
    }

    public function withStatus(UserStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }
}
