<?php

namespace KolayBi\Numerator\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class OutOfBoundsException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.out_of_bounds'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
