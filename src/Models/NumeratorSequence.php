<?php

namespace KolayBi\Numerator\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $profile_id
 * @property string $model_type
 * @property string $model_id
 * @property string $formatted_number
 *
 * @property-read NumeratorProfile $profile
 */
class NumeratorSequence extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $table = 'numerator_sequences';

    protected $guarded = [];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(NumeratorProfile::class, 'profile_id', 'id');
    }
}
