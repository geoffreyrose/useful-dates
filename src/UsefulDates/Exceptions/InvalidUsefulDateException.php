<?php

namespace UsefulDates\Exceptions;

class InvalidUsefulDateException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Useful Dates must extend \UsefulDates\Abstracts\UsefulDateAbstract");
    }
}
