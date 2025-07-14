<?php

namespace KolayBi\Numerator\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidFormatException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.invalid_format'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
