<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;
use KolayBi\Numerator\Traits\TenantIdScopeTrait;
use KolayBi\Numerator\Utils\Formatter;

/**
 * @property string $id
 * @property string $type_id
 * @property string $prefix
 * @property string $format
 * @property int    $start
 * @property int    $counter
 *
 * @property-read string                        $formattedNumber
 * @property-read NumeratorType                 $type
 * @property-read Collection<NumeratorSequence> $sequences
 */
class NumeratorProfile extends Model
{
    use HasUlids;
    use TenantIdScopeTrait;

    protected $table = 'numerator_profiles';

    protected $attributes = [
        'format' => NumeratorFormatVariable::NUMBER->value,
    ];

    public function formattedNumber(): Attribute
    {
        return new Attribute(
            get: fn(mixed $value, array $attributes) => Formatter::format(
                Arr::get($attributes, 'format'),
                Arr::get($attributes, 'counter'),
                Arr::get($attributes, 'prefix'),
            ),
        );
    }

    public function type(): HasOne
    {
        return $this->hasOne(NumeratorType::class, 'id', 'type_id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(NumeratorSequence::class, 'profile_id', 'id');
    }
}
