<?php

namespace KolayBi\Numerator\Exceptions;

use Illuminate\Support\Facades\Lang;

class NumberWithThisFormatExistsExceptionAbstract extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(Lang::get('messages.number_with_this_format_exists'));
    }
}
