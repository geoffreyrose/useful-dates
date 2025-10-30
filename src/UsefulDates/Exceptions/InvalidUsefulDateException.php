<?php

namespace UsefulDates\Exceptions;

class InvalidUsefulDateException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Useful Dates must extend \UsefulDates\Abstracts\UsefulDateAbstract");
    }
}
