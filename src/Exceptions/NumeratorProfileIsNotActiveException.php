<?php

namespace KolayBi\Numerator\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NumeratorProfileIsNotActiveException extends AbstractNumeratorException
{
    public function __construct()
    {
        parent::__construct(__('numerator::exceptions.numerator_profile_is_not_active'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
