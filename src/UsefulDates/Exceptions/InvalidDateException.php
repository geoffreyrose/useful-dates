<?php

namespace UsefulDates\Exceptions;

class InvalidDateException extends \Exception
{
    public function __construct($date)
    {
        parent::__construct("'\$date' must be a instance of Carbon\Carbon, DateTime or a date string. '" . gettype($date) . "' given");
    }
}
