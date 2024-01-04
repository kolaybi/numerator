<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $type_id
 * @property string $prefix
 * @property string $format
 * @property int    $start
 * @property int    $counter
 *
 * @property-read NumeratorType                 $type
 * @property-read Collection<NumeratorSequence> $sequences
 */
class NumeratorProfile extends Model
{
    use HasUlids;

    protected $table = 'numerator_profiles';

    protected $attributes = [
        'format' => NumeratorFormatVariable::NUMBER->value,
    ];

    public function formattedNumber(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => NumberFormatter::format($this, $this->counter),
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
