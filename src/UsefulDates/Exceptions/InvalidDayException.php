<?php

namespace UsefulDates\Exceptions;

class InvalidDayException extends \RuntimeException
{
    /**
     * Create a new InvalidDayException.
     *
     * @param  mixed  $day  The invalid day value passed to setBusinessDays().
     */
    public function __construct(mixed $day)
    {
        $dayString = is_scalar($day) ? (string) $day : '';
        parent::__construct("'\$day' must be an int from 0 to 6 or a Carbon day of week constant '(" . gettype($day) . ') ' . $dayString . "' given");
    }
}
