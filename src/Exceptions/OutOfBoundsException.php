<?php

namespace KolayBi\Numerator\Exceptions;

use Illuminate\Support\Facades\Lang;

class OutOfBoundsException extends NumeratorException
{
    public function __construct()
    {
        parent::__construct(Lang::get('messages.out_of_bounds'));
    }
}
