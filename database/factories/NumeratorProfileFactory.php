<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KolayBi\Numerator\Models\NumeratorProfile;
use KolayBi\Numerator\Models\NumeratorType;
use KolayBi\Numerator\Utils\FormatUtil;

/**
 * @extends Factory<NumeratorProfile>
 */
class NumeratorProfileFactory extends Factory
{
    protected $model = NumeratorProfile::class;

    public function definition(): array
    {
        $tenantIdColumn = Config::get('numerator.database.tenant_id_column');
        $defaultIsActiveStatus = Config::get('numerator.database.default_profile_is_active');

        return [
            'prefix'        => fake()->optional()->randomElement([Str::random(3)]),
            'suffix'        => fake()->optional()->randomElement([Str::random(3)]),
            'pad_length'    => fake()->optional()->numberBetween(0, 255),
            'is_active'     => $defaultIsActiveStatus,
            $tenantIdColumn => strtolower((string) Str::ulid()),
        ];
    }

    public function withRequired(): static
    {
        return $this->withType(NumeratorTypeFactory::new()->createOne());
    }

    public function withType(NumeratorType $numeratorType): static
    {
        return $this->state(fn(array $attributes) => [
            'type_id' => $numeratorType->id,
            'start'   => $numeratorType->min,
            'counter' => fake()->numberBetween($numeratorType->min, $numeratorType->max),
        ]);
    }

    public function withFormat(array $formats, bool $includeNumberFormat = true): static
    {
        return $this->state(fn(array $attributes) => [
            'format' => FormatUtil::serializeFormat($formats, $includeNumberFormat),
        ]);
    }

    public function active(bool $isActive = true): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => $isActive,
        ]);
    }
}
