<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\Exceptions\InvalidUsefulDateException;

trait Dates
{
    /**
     * Add a UsefulDate definition by class name.
     *
     * The class must extend UsefulDateAbstract. The instance will have its
     * current date and current useful-date context set to the instance's
     * current context date.
     *
     * @param  class-string  $date  Fully-qualified class name of the UsefulDate to add.
     * @return self Fluent interface.
     *
     * @throws InvalidUsefulDateException When the class does not extend UsefulDateAbstract.
     */
    public function add(string $date): self
    {
        if ($this->getTopParentClass($date) !== UsefulDateAbstract::class) {
            throw new InvalidUsefulDateException;
        }

        $date = new $date;
        $date->setCurrentDate($this->date);
        $date->setCurrentUsefulDate($this->date);
        $this->usefulDates[] = $date;

        return $this;
    }

    /**
     * Add a simple one-off or repeating date by name and base date.
     *
     * This creates an internal UsefulDate implementation that repeats according to the provided options.
     *
     * @param  string  $name  Human-friendly name for the date (e.g., "My Birthday").
     * @param  Carbon  $date  Prototype date whose month/day are used for each occurrence.
     * @param  bool  $isRepeated  Whether the date repeats beyond its start year (default true).
     * @param  RepeatFrequency  $repeatFrequency  NONE|MONTHLY|YEARLY|CUSTOM (default YEARLY).
     * @param  int  $startYear  The first calendar year in which the date is considered (default 1).
     * @return self Fluent interface.
     */
    public function addDate(string $name, Carbon $date, bool $isRepeated = true, RepeatFrequency $repeatFrequency = RepeatFrequency::YEARLY, ?int $startYear = null): self
    {
        $class = new class($name, $date, $isRepeated, $repeatFrequency, $startYear) extends \UsefulDates\Abstracts\UsefulDateAbstract
        {
            /**
             * Construct a simple repeating useful-date definition.
             *
             * @param  string  $name  Human-friendly name for the date.
             * @param  Carbon  $date  Prototype date whose month/day are used for each occurrence.
             * @param  bool  $isRepeated  Whether the date repeats beyond its start year.
             * @param  RepeatFrequency  $repeatFrequency  NONE|MONTHLY|YEARLY|CUSTOM.
             * @param  int  $startYear  The first calendar year in which the date is considered.
             */
            public function __construct(string $name, Carbon $date, bool $isRepeated, RepeatFrequency $repeatFrequency, ?int $startYear)
            {
                $startYear = $startYear ?? $date->year;
                $this->name = $name;
                $this->is_repeated = $isRepeated;
                if ($this->is_repeated === false) {
                    $this->repeat_frequency = RepeatFrequency::NONE;
                    $this->start_date = Carbon::createFromFormat('Y-m-d H:i:s', "{$date->year}-{$date->month}-{$date->day} 00:00:00");
                } else {
                    $this->repeat_frequency = $repeatFrequency;
                    $this->start_date = Carbon::createFromFormat('Y-m-d H:i:s', "{$startYear}-{$date->month}-{$date->day} 00:00:00");
                }

            }

            /**
             * Get the occurrence date for the current context year using the stored month/day.
             *
             * @return Carbon The computed occurrence date at 00:00:00 UTC for the current context year.
             */
            public function date(): Carbon
            {
                if ($this->repeat_frequency === RepeatFrequency::MONTHLY) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->currentDate->year}-{$this->currentDate->month}-{$this->start_date->day} 00:00:00");
                } else {
                    return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->currentDate->year}-{$this->start_date->month}-{$this->start_date->day} 00:00:00");
                }

            }
        };

        $class->setCurrentDate($this->date);
        $class->setCurrentUsefulDate($this->date);
        $this->usefulDates[] = $class;

        return $this;
    }
}
