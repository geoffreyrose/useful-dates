<?php

namespace UsefulDates\Exceptions;

class InvalidDateFormatException extends \RuntimeException
{
    /**
     * Create a new InvalidDateFormatException.
     */
    public function __construct()
    {
        parent::__construct('Invalid Date Format');
    }
}
