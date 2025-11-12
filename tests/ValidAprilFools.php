<?php

namespace Tests;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

class ValidAprilFools extends \UsefulDates\Abstracts\UsefulDateAbstract
{
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
