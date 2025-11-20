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

    public ?Carbon $start_date {
        set => $this->start_date = $value;
        get => $this->start_date;
    }

    public ?Carbon $end_date {
        set => $this->end_date = $value;
        get => $this->end_date;
    }

    protected Carbon $currentDate;

    public function setCurrentDate(Carbon $currentDate): self
    {
        $this->currentDate = $currentDate;

        return $this;
    }

    public function daysAway(): int
    {
        return (int) ceil(Carbon::now()->diffInDays($this->usefulDate()));
    }

    public function usefulDate(): ?Carbon
    {
        $date = $this->date();
        if ($this->start_date && $date->lt($this->start_date)) {
            return null;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return null;
        }

        return match ($this->repeat_frequency) {
            RepeatFrequency::NONE => $date?->isBirthday($this->currentDate) ? $date : null,
            RepeatFrequency::MONTHLY => $this->isWithinMonthlyRange() && $date?->isBirthday($this->currentDate) ? $date : null,
            RepeatFrequency::YEARLY => $this->isWithinYearlyRange() && $date?->isBirthday($this->currentDate) ? $date : null,
            RepeatFrequency::CUSTOM => $date,
        };
    }

    private function isWithinMonthlyRange(): bool
    {
        if (!$this->start_date) {
            return true;
        }

        return $this->currentDate->year > $this->start_date->year
            || ($this->currentDate->year === $this->start_date->year
                && $this->currentDate->month >= $this->start_date->month);
    }

    private function isWithinYearlyRange(): bool
    {
        if (!$this->start_date) {
            return true;
        }

        return $this->currentDate->year >= $this->start_date->year;
    }
}
