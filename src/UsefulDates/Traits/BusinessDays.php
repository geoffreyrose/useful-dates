<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;
use UsefulDates\Exceptions\BusinessDaysNotConfiguredException;
use UsefulDates\Exceptions\InvalidDayException;

trait BusinessDays
{
    /** @var int[] */
    public array $businessDays = [1, 2, 3, 4, 5] {
        set => $this->businessDays = $value;
    }

    /**
     * Determine whether the current businessDays represent the standard Monday–Friday.
     *
     * @return bool True if business days are [1,2,3,4,5]; false otherwise.
     */
    public function isStandardBusinessDays(): bool
    {
        if ($this->businessDays != [1, 2, 3, 4, 5]) {
            return false;
        }

        return true;
    }

    /**
     * Define which days of the week are considered business days.
     *
     * @param  array<int, int>  $days  List of days of week that are business days.
     * @return self Fluent interface.
     *
     * @throws InvalidDayException If any provided day is not within 0..6.
     */
    public function setBusinessDays(array $days): self
    {
        foreach ($days as $day) {
            if (!is_int($day) || !in_array($day, [0, 1, 2, 3, 4, 5, 6])) {
                throw new InvalidDayException($day);
            }
        }

        $this->businessDays = $days;

        return $this;
    }

    /**
     * Get the configured business days.
     *
     * @return int[] List of days of week considered business days (0=Sun..6=Sat).
     */
    public function getBusinessDays(): array
    {
        return $this->businessDays;
    }

    /**
     * Determine whether the given date is a business day.
     *
     * If no date is provided, the current context date is used.
     *
     * @param  Carbon|null  $date  Optional date to check. Defaults to current context date.
     * @return bool True if the date falls on a configured business day; false otherwise.
     */
    public function isBusinessDay(?Carbon $date = null): bool
    {
        if (!$date) {
            $date = $this->date;
        }

        return in_array($date->dayOfWeek, $this->businessDays);
    }

    /**
     * Get the next business day after the current context date.
     *
     * @return Carbon|null The next date that falls on a configured business day.
     */
    public function nextBusinessDay(): ?Carbon
    {
        if ($this->businessDays === []) {
            return null;
        }

        $day = $this->date->copy();
        $next = null;
        while (!$next) {
            $day->addDay();

            $next = $this->isBusinessDay($day);
        }

        return $day;
    }

    /**
     * Get the previous business day before the current context date.
     *
     * @return Carbon|null The previous date that falls on a configured business day.
     */
    public function prevBusinessDay(): ?Carbon
    {
        if ($this->businessDays === []) {
            return null;
        }

        $day = $this->date->copy();
        $prev = null;
        while (!$prev) {
            $day->subDay();

            $prev = $this->isBusinessDay($day);
        }

        return $day;
    }

    /**
     * Get today if it is a business day; otherwise, return the previous business day.
     *
     * @return Carbon The date that is either today or the most recent business day before today.
     *
     * @throws BusinessDaysNotConfiguredException
     */
    public function todayOrPreviousBusinessDay(): Carbon
    {
        if ($this->isBusinessDay($this->date)) {
            return $this->date;
        }
        $result = $this->prevBusinessDay();
        if ($result === null) {
            throw new BusinessDaysNotConfiguredException;
        }

        return $result;
    }

    /**
     * Get today if it is a business day; otherwise, return the next business day.
     *
     * @return Carbon The date that is either today or the next business day after today.
     *
     * @throws BusinessDaysNotConfiguredException
     */
    public function todayOrNextBusinessDay(): Carbon
    {
        if ($this->isBusinessDay($this->date)) {
            return $this->date;
        }
        $result = $this->nextBusinessDay();
        if ($result === null) {
            throw new BusinessDaysNotConfiguredException;
        }

        return $result;
    }
}
