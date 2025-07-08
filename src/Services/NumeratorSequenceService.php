<?php

namespace KolayBi\Numerator\Services;

use Carbon\Carbon;
use KolayBi\Numerator\Exceptions\NumberWithThisFormatExistsException;
use KolayBi\Numerator\Exceptions\NumeratorProfileIsNotActiveException;
use KolayBi\Numerator\Exceptions\OutOfBoundsException;
use KolayBi\Numerator\Models\NumeratorSequence;

class NumeratorSequenceService
{
    /**
     * @throws NumberWithThisFormatExistsException
     * @throws NumeratorProfileIsNotActiveException
     * @throws OutOfBoundsException
     */
    public function createNumeratorSequence(string $numeratorType, string $modelType, string $modelId, string $formattedNumber, bool $skipActiveCheck = false): NumeratorSequence
    {
        $numeratorProfileService = new NumeratorProfileService();
        $profile = $numeratorProfileService->findNumeratorProfileByType($numeratorType, lock: true);

        if (!$skipActiveCheck && !$profile->is_active) {
            throw new NumeratorProfileIsNotActiveException();
        }

        if (
            is_numeric($formattedNumber)
            && !$numeratorProfileService->isWithinInterval($profile->type, $formattedNumber)
        ) {
            throw new OutOfBoundsException();
        }

        if ($profile->reuse_if_deleted) {
            if ($numeratorProfileService->hasActiveSequence($profile, $formattedNumber, excludedModelId: $modelId)) {
                throw new NumberWithThisFormatExistsException();
            }
        } else {
            if ($numeratorProfileService->hasSequence($profile, $formattedNumber, excludedModelId: $modelId)) {
                throw new NumberWithThisFormatExistsException();
            }
        }

        /** @var NumeratorSequence $sequence */
        $sequence = NumeratorSequence::updateOrCreate(
            [
                'model_type'       => $modelType,
                'model_id'         => $modelId,
                'profile_id'       => $profile->id,
                'formatted_number' => $formattedNumber,
            ],
            [
                'updated_at' => Carbon::now(),
            ],
        );

        if ($profile->formattedNumber === $formattedNumber) {
            $numeratorProfileService->advanceCounter($profile, ++$profile->counter);
        }

        return $sequence;
    }

    public function deleteNumeratorSequence(NumeratorSequence|string $sequence): void
    {
        $sequence = $this->findNumeratorSequence($sequence, lock: true);

        $sequence->delete();
    }

    public function findNumeratorSequence(NumeratorSequence|string $sequence, ?bool $lock = null): NumeratorSequence
    {
        if (is_string($sequence)) {
            return NumeratorSequence::lock($lock)->findOrFail($sequence);
        }

        if ($sequence instanceof NumeratorSequence && $lock) {
            return NumeratorSequence::lock($lock)->findOrFail($sequence->id);
        }

        return $sequence;
    }
}
