<?php

namespace KolayBi\Numerator\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

class Formatter
{
    public static function format(string $format, int $number, ?string $prefix = null, ?string $suffix = null, ?int $padLength = 0): string
    {
        $prefixSeparator = Config::get('numerator.database.prefix_separator');
        $suffixSeparator = Config::get('numerator.database.suffix_separator');
        $currentDate = Carbon::now();

        $formatVariables = [
            NumeratorFormatVariable::NUMBER->value      => Str::padLeft(value: $number, length: $padLength ?? 0, pad: 0),
            NumeratorFormatVariable::LONG_YEAR->value   => $currentDate->format('Y'),
            NumeratorFormatVariable::SHORT_YEAR->value  => $currentDate->format('y'),
            NumeratorFormatVariable::LONG_MONTH->value  => $currentDate->format('m'),
            NumeratorFormatVariable::SHORT_MONTH->value => $currentDate->format('n'),
            NumeratorFormatVariable::LONG_DAY->value    => $currentDate->format('d'),
            NumeratorFormatVariable::SHORT_DAY->value   => $currentDate->format('j'),
        ];

        return Str::of($prefix . $prefixSeparator)
            ->append(Str::replace(array_keys($formatVariables), array_values($formatVariables), $format))
            ->append($suffixSeparator, $suffix)
            ->ltrim($prefixSeparator)
            ->rtrim($suffixSeparator)
            ->trim()
            ->toString();
    }
}
