<?php

namespace UsefulDates\Interfaces;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

interface UsefulDateInterface
{
    public string $name {
        set;
        get;
    }

    public array $additional_search_names {
        set;
        get;
    }

    public bool $is_repeated {
        set;
        get;
    }

    public RepeatFrequency $repeat_frequency {
        set;
        get;
    }

    public ?Carbon $start_date {
        set;
        get;
    }

    public ?Carbon $end_date {
        set;
        get;
    }

    public function setCurrentDate(Carbon $currentDate): self;

    public function date(): ?Carbon;

    public function usefulDate(): ?Carbon;

    public function daysAway(): int;
}
