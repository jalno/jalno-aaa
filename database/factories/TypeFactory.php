<?php

namespace Jalno\AAA\Database\Factories;

use Jalno\AAA\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition()
    {
        return [
            'meta' => ['key' => 'value'],
        ];
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
}
