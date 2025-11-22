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
        $this->start_date = Carbon::createFromFormat('Y-m-d H:i:s', '1582-04-01 00:00:00');
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->currentDate->year}-04-01 00:00:00");
    }
}
