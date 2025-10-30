<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;
use UsefulDates\Exceptions\InvalidDayException;

trait BusinessDays
{
    public array $businessDays = [1, 2, 3, 4, 5] {
        set => $this->businessDays = $value;
    }

    /**
     * Check if using standard business days
     */
    public function isStandardBusinessDays(): bool
    {
        if ($this->businessDays != [1, 2, 3, 4, 5]) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidDayException
     */
    public function setBusinessDays(array $days): self
    {
        foreach ($days as $day) {
            if (!in_array($day, [0, 1, 2, 3, 4, 5, 6])) {
                throw new InvalidDayException($day);
            }
        }

        $this->businessDays = $days;

        return $this;
    }

    /**
     * Get business days
     */
    public function getBusinessDays(): array
    {
        return $this->businessDays;
    }

    /**
     * Check if date is a business day
     */
    public function isBusinessDay($date = null): bool
    {
        if (!$date) {
            $date = $this->date;
        }

        return in_array($date->dayOfWeek, $this->businessDays);
    }

    /**
     * Next business day
     *
     * @return Carbon Carbon Date object
     */
    public function nextBusinessDay(): Carbon
    {
        $day = $this->date->copy();
        $next = null;
        while (!$next) {
            $day->addDay();

            $next = $this->isBusinessDay($day);
        }

        return $day;
    }

    /**
     * Previous business day
     *
     * @return Carbon Carbon Date object
     */
    public function prevBusinessDay(): Carbon
    {
        $day = $this->date->copy();
        $prev = null;
        while (!$prev) {
            $day->subDay();

            $prev = $this->isBusinessDay($day);
        }

        return $day;
    }
}
