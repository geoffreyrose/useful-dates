<?php

namespace UsefulDates;

use Carbon\Carbon;
use DateTime;
use Throwable;
use UsefulDates\Exceptions\InvalidDateException;
use UsefulDates\Traits\BusinessDays;
use UsefulDates\Traits\Dates;
use UsefulDates\Traits\Extensions;
use UsefulDates\Traits\Info;
use UsefulDates\Traits\Intervals;

class UsefulDates
{
    use BusinessDays;
    use Dates;
    use Extensions;
    use Info;
    use Intervals;

    private array $usefulDates = [];

    private array $customMethods = [];

    public Carbon $date {
        get => $this->date;
    }

    public function __construct()
    {
        $this->setDate(Carbon::now());
    }

    /**
     * Set the working date/time context for all useful date calculations.
     *
     * The value is normalized to UTC. Accepts Carbon, DateTime, or any string
     * understood by Carbon::create().
     *
     * @param  Carbon|DateTime|string  $date  The date/time to set as context.
     * @return self Fluent interface.
     *
     * @throws InvalidDateException When the provided string cannot be parsed into a date.
     */
    public function setDate(Carbon|DateTime|string $date): self
    {
        if (!$date) {
            throw new InvalidDateException($date);
        }

        if ($date instanceof Carbon) {
            $this->date = $date->copy();
        } elseif ($date instanceof DateTime) {
            $this->date = Carbon::create($date);
        } else {
            try {
                $this->date = Carbon::create($date);
            } catch (Throwable) {
                throw new InvalidDateException($date);
            }
        }

        $this->date->shiftTimezone('UTC');

        foreach ($this->usefulDates as $usefulDate) {
            $usefulDate->setCurrentUsefulDate($this->date);
        }

        return $this;
    }

    /**
     * Get the top-most parent class name for the given class name.
     *
     * @param  string  $className  Fully-qualified class name to inspect.
     * @return string|null The top-most parent class name, or null if none or on error.
     */
    private function getTopParentClass(string $className): ?string
    {
        try {
            $currentClass = $className;
            $topParent = null;

            while ($parent = get_parent_class($currentClass)) {
                $topParent = $parent;
                $currentClass = $parent;
            }

            return $topParent;
        } catch (Throwable) {
            return null;
        }
    }
}
