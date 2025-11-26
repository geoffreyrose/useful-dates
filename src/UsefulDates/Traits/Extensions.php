<?php

namespace UsefulDates\Traits;

use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;
use UsefulDates\Exceptions\InvalidExtensionException;
use UsefulDates\Exceptions\InvalidUsefulDateException;

trait Extensions
{
    /**
     * Register an extension and import its provided useful dates and methods.
     *
     * The extension class must extend UsefulDatesExtensionAbstract. Any provided
     * useful dates must extend UsefulDateAbstract.
     *
     * @param  class-string  $extension  Fully-qualified extension class name.
     * @param  mixed|null  $options  Optional configuration passed to the extension when building dates.
     * @return self Fluent interface.
     *
     * @throws InvalidExtensionException|InvalidUsefulDateException When the extension or a provided date is invalid.
     */
    public function addExtension(string $extension, mixed $options = null): self
    {
        if ($this->getTopParentClass($extension) !== UsefulDatesExtensionAbstract::class) {
            throw new InvalidExtensionException;
        }

        foreach ($extension::usefulDates($options) as $dateToAdd) {
            if ($this->getTopParentClass($dateToAdd) !== UsefulDateAbstract::class) {
                throw new InvalidUsefulDateException;
            }

            $this->add($dateToAdd);
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
     * Handle dynamic method calls registered by extensions.
     *
     * If an extension has exposed a method via methods(), the call will be
     * dispatched to the stored callable. Otherwise a BadMethodCallException
     * is thrown.
     *
     * @param  string  $name  The method name being invoked.
     * @param  array<int, mixed>  $arguments  Arguments passed to the dynamic method.
     * @return mixed The result of the callable.
     *
     * @throws \BadMethodCallException When no dynamic method exists for $name.
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
