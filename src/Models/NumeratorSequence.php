<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $profile_id
 * @property int    $number
 * @property string $formatted_number
 *
 * @property-read NumeratorProfile $profile
 */
class NumeratorSequence extends Model
{
    use HasUlids;

    protected $table = 'numerator_sequences';

    protected $guarded = [];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(NumeratorProfile::class, 'profile_id', 'id');
    }
}
