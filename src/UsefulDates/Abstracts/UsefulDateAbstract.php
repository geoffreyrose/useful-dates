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

    public function setCurrentDate(Carbon $currentDate): self
    {
        $this->currentDate = $currentDate;

        return $this;
    }

    public function setCurrentUsefulDate(Carbon $currentDate): self
    {
        $this->currentUsefulDate = $currentDate;

        return $this;
    }

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
