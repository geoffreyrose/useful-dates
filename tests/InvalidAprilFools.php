<?php

namespace Tests;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

class InvalidAprilFools
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

    public function __construct()
    {
        $this->name = "April Fools' Day";
        $this->additional_search_names = ["APRIL FOOL'S DAY", "APRIL FOOLS' DAY", 'APRIL FOOLS DAY', 'APRIL FOOLS'];
        $this->start_date = Carbon::create(1582, 4, 1, 0, 0, 0);
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
    }
}
