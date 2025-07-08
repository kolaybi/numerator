<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

/**
 * @property string $id
 * @property string $name
 * @property string $group
 * @property int    $min
 * @property int    $max
 * @property string $prefix
 * @property string $suffix
 * @property string $format
 * @property int    $pad_length
 *
 * @property-read Collection<NumeratorProfile> $profiles
 */
class NumeratorType extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $table = 'numerator_types';

    protected $guarded = [];

    public function min(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => $value ?? 0,
        );
    }

    public function max(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => $value ?? PHP_INT_MAX,
        );
    }

    public function format(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => $value ?? NumeratorFormatVariable::NUMBER->value,
        );
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(NumeratorProfile::class, 'type_id', 'id');
    }
}
