<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property int    $min
 * @property int    $max
 */
class NumeratorType extends Model
{
    use HasUlids;

    protected $table = 'numerator_types';

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
}
