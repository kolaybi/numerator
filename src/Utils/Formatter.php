<?php

namespace KolayBi\Numerator\Utils;

use Carbon\Carbon;
use Illuminate\Support\Str;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

class Formatter
{
    public static function format(string $format, int $number, ?string $prefix = null): string
    {
        $currentDate = Carbon::now();

        $formatVariables = [
            NumeratorFormatVariable::NUMBER->value      => $number,
            NumeratorFormatVariable::LONG_YEAR->value   => $currentDate->format('Y'),
            NumeratorFormatVariable::SHORT_YEAR->value  => $currentDate->format('y'),
            NumeratorFormatVariable::LONG_MONTH->value  => $currentDate->format('m'),
            NumeratorFormatVariable::SHORT_MONTH->value => $currentDate->format('n'),
            NumeratorFormatVariable::LONG_DAY->value    => $currentDate->format('d'),
            NumeratorFormatVariable::SHORT_DAY->value   => $currentDate->format('j'),
        ];

        return Str::of($prefix . '-')
            ->append(Str::replace(array_keys($formatVariables), array_values($formatVariables), $format))
            ->ltrim('-')
            ->trim()
            ->toString();
    }
}
