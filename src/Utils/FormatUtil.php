<?php

namespace KolayBi\Numerator\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

final class FormatUtil
{
    public static function isValidFormat(?string $format, bool $nullable = false): bool
    {
        if (is_null($format)) {
            return $nullable;
        }

        return Str::contains($format, NumeratorFormatVariable::NUMBER->value);
    }

    public static function generateRandomFormat(): ?string
    {
        return self::serializeFormat(Arr::random(NumeratorFormatVariable::cases(), rand(0, count(NumeratorFormatVariable::cases()))));
    }

    /**
     * @param array<NumeratorFormatVariable|string>|null $formats
     */
    public static function serializeFormat(?array $formats, bool $includeNumberFormat = true): ?string
    {
        if (empty($formats)) {
            return null;
        }

        $format = Str::of('')->toString();

        if (
            $includeNumberFormat
            && !(in_array(NumeratorFormatVariable::NUMBER->value, $formats) || in_array(NumeratorFormatVariable::NUMBER, $formats))
        ) {
            $formats[] = NumeratorFormatVariable::NUMBER->value;
        }

        foreach ($formats as $item) {
            $format .= ($item instanceof NumeratorFormatVariable) ? $item->value : $item;
        }

        return $format;
    }
}
