<?php

namespace UsefulDates\Abstracts;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\Interfaces\UsefulDateInterface;

abstract class UsefulDateAbstract implements UsefulDateInterface
{
    public string $name {
        set => $this->name = $value;
        get => $this->name;
    }

    public array $additional_search_names = [] {
        set => $this->additional_search_names = $value;
        get => $this->additional_search_names;
    }

    public bool $is_repeated = false {
        set => $this->is_repeated = $value;
        get => $this->is_repeated;
    }

    public RepeatFrequency $repeat_frequency = RepeatFrequency::NONE {
        set => $this->repeat_frequency = $value;
        get => $this->repeat_frequency;
    }

    public ?Carbon $start_date = null {
        set => $this->start_date = $value;
        get => $this->start_date;
    }

    public ?Carbon $end_date = null {
        set => $this->end_date = $value;
        get => $this->end_date;
    }

    protected Carbon $currentDate;

    protected Carbon $currentUsefulDate;

    /**
     * Set the date used to evaluate the definition for a specific occurrence.
     *
     * @param  Carbon  $currentDate  The date context for evaluating this useful date.
     * @return self Fluent interface.
     */
    public function setCurrentDate(Carbon $currentDate): self
    {
        $this->currentDate = $currentDate;

        return $this;
    }

    /**
     * Set the date used to compute relative values such as daysAway().
     *
     * @param  Carbon  $currentDate  The date context for relative calculations.
     * @return self Fluent interface.
     */
    public function setCurrentUsefulDate(Carbon $currentDate): self
    {
        $this->currentUsefulDate = $currentDate;

        return $this;
    }

    /**
     * Get the number of days between the relative context date and this occurrence.
     *
     * Positive values mean the occurrence is in the future; negative values mean
     * it is in the past. Zero means the occurrence is today.
     *
     * @return int Signed number of days until/since the useful date occurrence.
     */
    public function daysAway(): int
    {
        $ceil = ceil($this->currentUsefulDate->diffInDays($this->usefulDate()));
        if ($ceil > 0) {
            return $ceil;
        } elseif ($ceil <= -1) {
            return $ceil;
        } else {
            return 0;
        }
    }

    /**
     * Compute the occurrence date for the current context if it is considered useful.
     *
     * Applies the repeat frequency rules and optional start/end ranges.
     *
     * @return Carbon|null The occurrence date matching the current context, or null if not useful.
     */
    public function usefulDate(): ?Carbon
    {
        $date = $this->date();
        if (!$date) {
            return null;
        }

        if ($this->repeat_frequency === RepeatFrequency::CUSTOM) {
            return $date;
        }

        $isBirthday = $date->isBirthday($this->currentDate);
        if (!$isBirthday) {
            return null;
        }

        return match ($this->repeat_frequency) {
            RepeatFrequency::NONE => $date->year === $this->start_date->year ? $date : null,
            RepeatFrequency::MONTHLY => $this->isWithinMonthlyRange() ? $date : null,
            RepeatFrequency::YEARLY => $this->isWithinYearlyRange() ? $date : null,
        };
    }

    /**
     * Determine if the current context month is within the configured monthly range.
     *
     * Applies start_date and end_date month/year bounds where provided.
     *
     * @return bool True if the current month lies within [start, end]; false otherwise.
     */
    private function isWithinMonthlyRange(): bool
    {
        if (!$this->start_date) {
            return true;
        }

        $currentYear = $this->currentDate->year;
        $currentMonth = $this->currentDate->month;
        $startYear = $this->start_date->year;
        $startMonth = $this->start_date->month;

        if ($currentYear < $startYear || ($currentYear === $startYear && $currentMonth < $startMonth)) {
            return false;
        }

        if (!$this->end_date) {
            return true;
        }

        $endYear = $this->end_date->year;
        $endMonth = $this->end_date->month;

        return $currentYear < $endYear || ($currentYear === $endYear && $currentMonth <= $endMonth);
    }

    /**
     * Determine if the current context year is within the configured yearly range.
     *
     * Applies start_date and end_date year bounds where provided.
     *
     * @return bool True if the current year lies within [start, end]; false otherwise.
     */
    private function isWithinYearlyRange(): bool
    {
        if (!$this->start_date) {
            return true;
        }

        $currentYear = $this->currentDate->year;

        if ($currentYear < $this->start_date->year) {
            return false;
        }

        return !$this->end_date || $currentYear <= $this->end_date->year;
    }
}
