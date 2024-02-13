<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Utils\FormatUtil;

/**
 * @extends Factory<NumeratorType>
 */
class NumeratorTypeFactory extends Factory
{
    protected $model = NumeratorType::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->unique()->word(),
            'min'        => fake()->optional()->numberBetween(1, 100),
            'prefix'     => fake()->optional()->randomElement([Str::random(3)]),
            'suffix'     => fake()->optional()->randomElement([Str::random(3)]),
            'format'     => FormatUtil::generateRandomFormat(),
            'pad_length' => fake()->optional()->numberBetween(0, 255),
        ];
    }

    public function withFormat(?array $formats, bool $includeNumberFormat = true): static
    {
        return $this->state(fn(array $attributes) => [
            'format' => FormatUtil::serializeFormat($formats, $includeNumberFormat),
        ]);
    }
}
