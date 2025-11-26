<?php

namespace UsefulDates\Exceptions;

class InvalidDateException extends \RuntimeException
{
    /**
     * Create a new InvalidDateException.
     *
     * @param  mixed  $date  The invalid value provided to setDate().
     */
    public function __construct($date)
    {
        parent::__construct("'\$date' must be a instance of Carbon\Carbon, DateTime or a date string. '" . gettype($date) . "' given");
    }
}
