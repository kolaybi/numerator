<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use KolayBi\Numerator\Models\NumeratorSequence;

/**
 * @extends Factory<NumeratorSequence>
 */
class NumeratorSequenceFactory extends Factory
{
    protected $model = NumeratorSequence::class;

    public function definition(): array
    {
        return [
            'model_type'       => fake()->word(),
            'model_id'         => strtolower((string) Str::ulid()),
            'formatted_number' => fake()->word(),
        ];
    }

    public function withRequired(): static
    {
        return $this->state(fn(array $attributes) => [
            'profile_id' => NumeratorProfileFactory::new()->withRequired(),
        ]);
    }
}
