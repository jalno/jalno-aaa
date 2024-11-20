<?php

namespace Jalno\AAA\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jalno\AAA\Models\Country;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition()
    {
        return [
            'code' => fake()->unique()->countryCode(),
            'name' => fake()->country(),
            'dialing_code' => fake()->unique()->numberBetween(0, 999),
        ];
    }
}
