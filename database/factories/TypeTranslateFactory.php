<?php

namespace Jalno\AAA\Database\Factories;

use dnj\AAA\Models\Type;
use dnj\AAA\Models\TypeTranslate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeTranslate>
 */
class TypeTranslateFactory extends Factory
{
    protected $model = TypeTranslate::class;

    public function definition()
    {
        $locale = fake()->locale();

        return [
            'type_id' => Type::factory(),
            'locale' => substr($locale, 0, 2),
            'title' => fake($locale)->words(2, true),
        ];
    }

    public function withLocale(string $locale): static
    {
        return $this->state(fn () => [
            'locale' => $locale,
        ]);
    }

    public function withType(int|Type $type): static
    {
        return $this->state(fn () => [
            'type_id' => $type,
        ]);
    }

    public function withTitle(array $title): static
    {
        return $this->state(fn () => [
            'title' => $title,
        ]);
    }
}
