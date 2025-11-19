<?php

namespace UsefulDates\Traits;

use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;
use UsefulDates\Exceptions\InvalidExtensionException;
use UsefulDates\Exceptions\InvalidUsefulDateException;

trait Extensions
{
    /**
     * @throws InvalidExtensionException|InvalidUsefulDateException
     */
    public function addExtension($extension): self
    {
        if ($this->getTopParentClass($extension) !== UsefulDatesExtensionAbstract::class) {
            throw new InvalidExtensionException;
        }

        foreach ($extension::usefulDates() as $dateToAdd) {
            if ($this->getTopParentClass($dateToAdd) !== UsefulDateAbstract::class) {
                throw new InvalidUsefulDateException;
            }

            $this->add(new $dateToAdd);
        }

        if ($extension::$hasMethods) {
            $ext = new $extension($this);
            foreach ($ext->methods() as $methodName => $callable) {
                $this->customMethods[$methodName] = $callable;
            }
        }

        return $this;
    }

    /**
     * Handle dynamic method calls
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (isset($this->customMethods[$name])) {
            $callable = $this->customMethods[$name];

            return call_user_func_array($callable, $arguments);
        }

        throw new \BadMethodCallException("Method {$name} does not exist");
    }
}
