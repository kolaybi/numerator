<?php

namespace KolayBi\Numerator\Exceptions;

class OutOfBoundsException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.out_of_bounds'));
    }
}
