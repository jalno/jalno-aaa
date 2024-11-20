<?php

namespace Jalno\AAA\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jalno\AAA\Models\Type;
use Jalno\AAA\Models\TypeAbility;

/**
 * @extends Factory<TypeAbility>
 */
class TypeAbilityFactory extends Factory
{
    protected $model = TypeAbility::class;

    public function definition()
    {
        return [
            'type' => Type::factory(),
            'name' => fake()->words(3, true),
        ];
    }

    public function withType(int|Type $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }
}
