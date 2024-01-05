<?php

namespace KolayBi\Numerator\Exceptions;

class NumberWithThisFormatExistsException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.number_with_this_format_exists'));
    }
}
