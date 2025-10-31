<?php

namespace UsefulDates\Exceptions;

class InvalidExtensionException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Extension must extend '\UsefulDates\Abstracts\ExtensionAbstract'");
    }
}
