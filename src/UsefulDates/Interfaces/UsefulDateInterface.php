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

    /**
     * Set the date used to evaluate the definition for a specific occurrence.
     *
     * @param  Carbon  $currentDate  The date context for evaluating this useful date.
     * @return self Fluent interface.
     */
    public function setCurrentDate(Carbon $currentDate): self;

    /**
     * Get the occurrence date for the current context, ignoring repeat filters.
     *
     * @return Carbon|null The base date for the current context, or null if not applicable.
     */
    public function date(): ?Carbon;

    /**
     * Compute the occurrence date for the current context if it is considered useful.
     *
     * @return Carbon|null The occurrence date matching the current context, or null if not useful.
     */
    public function usefulDate(): ?Carbon;

    /**
     * Get the number of days between the relative context date and this occurrence.
     *
     * Positive values indicate the occurrence is in the future; negative values indicate the past.
     *
     * @return int Signed number of days until/since the occurrence.
     */
    public function daysAway(): int;
}
