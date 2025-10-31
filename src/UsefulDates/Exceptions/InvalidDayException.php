<?php

namespace UsefulDates\Exceptions;

class InvalidDayException extends \RuntimeException
{
    public function __construct($day)
    {
        parent::__construct("'\$day' must be an int from 0 to 6 or a Carbon day of week constant '(" . gettype($day) . ') ' . $day . "' given");
    }
}
