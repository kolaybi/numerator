<?php

namespace KolayBi\Numerator\Models;

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
}
