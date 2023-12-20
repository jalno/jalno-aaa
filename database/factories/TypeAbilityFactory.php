<?php

namespace dnj\AAA\Database\Factories;

use dnj\AAA\Models\Type;
use dnj\AAA\Models\TypeAbility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeAbility>
 */
class TypeAbilityFactory extends Factory
{
    protected $model = TypeAbility::class;

    public function definition()
    {
        return [
            'type_id' => Type::factory(),
            'name' => fake()->words(3, true),
        ];
    }

    public function withType(int|Type $type): static
    {
        return $this->state(fn () => [
            'type_id' => $type,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }
}
