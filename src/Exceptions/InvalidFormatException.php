<?php

namespace KolayBi\Numerator\Exceptions;

class InvalidFormatException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.invalid_format'));
    }
}
