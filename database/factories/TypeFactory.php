<?php

namespace Jalno\AAA\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jalno\AAA\Models\Type;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition()
    {
        return [
            'title' => fake()->jobTitle(),
        ];
    }

    public function withTitle(string $title): static
    {
        return $this->state(fn () => [
            'title' => $title,
        ]);
    }
}
