<?php

namespace KolayBi\Numerator\Services;

use KolayBi\Numerator\Exceptions\NumberWithThisFormatExistsExceptionAbstract;
use KolayBi\Numerator\Exceptions\OutOfBoundsExceptionAbstract;
use KolayBi\Numerator\Models\NumeratorSequence;

class NumeratorSequenceService
{
    /**
     * @throws NumberWithThisFormatExistsExceptionAbstract
     * @throws OutOfBoundsExceptionAbstract
     */
    public function createNumeratorSequence(string $numeratorType, string $formattedNumber): NumeratorSequence
    {
        $numeratorProfileService = new NumeratorProfileService();
        $profile = $numeratorProfileService->findNumeratorProfileByType($numeratorType, lock: true);

        if (
            is_numeric($formattedNumber)
            && !$numeratorProfileService->isWithinInterval($profile->type, $formattedNumber)
        ) {
            throw new OutOfBoundsExceptionAbstract();
        }

        if ($numeratorProfileService->hasSequence($profile, $formattedNumber)) {
            throw new NumberWithThisFormatExistsExceptionAbstract();
        }

        /** @var NumeratorSequence $sequence */
        $sequence = NumeratorSequence::create(
            [
                'profile_id'       => $profile->id,
                'number'           => $profile->counter,
                'formatted_number' => $formattedNumber,
            ],
        );

        if ($profile->formattedNumber === $formattedNumber) {
            $numeratorProfileService->advanceCounter($profile, ++$sequence->number);
        }

        return $sequence;
    }
}
