<div style="text-align: center;"> 

[![Latest Stable Version](https://img.shields.io/packagist/v/geoffreyrose/useful-dates?style=flat-square)](https://packagist.org/packages/geoffreyrose/useful-dates)
[![License](https://img.shields.io/github/license/geoffreyrose/useful-dates?style=flat-square)](https://github.com/geoffreyrose/useful-dates/blob/main/LICENSE)
</div>

# Useful Dates - PHP + Laravel Facade

**Work in progress, not ready for production. It may never be, just something I'm experimenting around with.**

A simple and elegant PHP library for working with commonly used or desired dates in your applications. UsefulDates helps you quickly identify and retrieve important dates like holidays, birthdays, paydays, and other significant calendar events.

This is an evolution of my other package [US Holidays](https://geoffreyrose.github.io/us-holidays/). 

You can add your own dates that can have as simple or complex logic as you want.

With useful days you can also create extensions (not implemented yet) that contain your country-specific holidays, family and friends birthdays, or anything else you want.

On it's one UsefulDates comes with no dates.  It is a clean base that you can extend with your own dates with methods to calculate dates on demand on a page or to save in a database.

### Requirements
* PHP 8.4+
* [Carbon](http://carbon.nesbot.com/)

### Usage

#### Installation
```
composer require geoffreyrose/useful-dates
```

### Basic Usage
```php
use UsefulDates\UsefulDates;

...

$usefulDates = new UsefulDates;
$usefulDates = $usefulDates->setDate(\Carbon\Carbon::now());

$usefulDates = $usefulDates->getNextUsefulDates(5);
```

### With Laravel Facade
Laravel uses Package Auto-Discovery, which doesn't require you to manually add the ServiceProvider and Facade.
```php
$usefulDates = UsefulDates::setDate(\Carbon\Carbon::now());
```


## Add a Useful Date

1. Create a new class that extends [`\UsefulDates\Abstracts\UsefulDateAbstract`](https://github.com/geoffreyrose/useful-dates/src/UsefulDates/Abstracts/UsefulDateAbstract.php) 

An example for April Fools' Day:
```php
<?php

namespace App;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

class AprilFools extends \UsefulDates\Abstracts\UsefulDateAbstract
{
    public function __construct()
    {
        $this->name = "April Fools' Day";
        $this->start_date = Carbon::create(1582, 4, 1, 0, 0, 0);;
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
    }
}
```

2. Use the `add` method to add the new class to the `UsefulDates` instance.

```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->add(new App\AprilFools);

$usefulDates = $usefulDates->getNextUsefulDates(5);
// this will return the next 5 Carbon dates of April Fools' Day since that is the only UsefulDate added.
```

Methods on `UsefulDaresAbstract``
* daysAway() // a positive or negative integer from your current date set



### Linting

```
 ./vendor/bin/pint   
```
