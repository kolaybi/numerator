<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use KolayBi\Numerator\Models\NumeratorType;

/**
 * @extends Factory<NumeratorType>
 */
class NumeratorTypeFactory extends Factory
{
    protected $model = NumeratorType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'min'  => fake()->optional()->numberBetween(1, 100),
            'max'  => fake()->optional()->numberBetween(100, 1000),
        ];
    }
}
