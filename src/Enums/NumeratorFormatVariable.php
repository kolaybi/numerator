<?php

namespace KolayBi\Numerator\Enums;

enum NumeratorFormatVariable: string
{
    case NUMBER = '%NUMBER';
    case LONG_YEAR = '%YYYY';
    case SHORT_YEAR = '%YY';
    case LONG_MONTH = '%MM';
    case SHORT_MONTH = '%M';
    case LONG_DAY = '%DD';
    case SHORT_DAY = '%D';
}
