<div style="text-align: center;"> 

[![Latest Stable Version](https://img.shields.io/packagist/v/geoffreyrose/useful-dates?style=flat-square)](https://packagist.org/packages/geoffreyrose/useful-dates)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/geoffreyrose/useful-dates/tests.yml?branch=main&style=flat-square)](https://github.com/geoffreyrose/useful-dates/actions?query=branch%3Amain)
[![License](https://img.shields.io/github/license/geoffreyrose/useful-dates?style=flat-square)](https://github.com/geoffreyrose/useful-dates/blob/main/LICENSE)

</div>

# Useful Dates

A simple and elegant PHP library for working with commonly used or desired dates in your applications. UsefulDates helps you quickly identify and retrieve important dates like holidays, birthdays, paydays, and other significant calendar events.

This is an evolution of [geoffreyrose/us-holidays](https://github.com/geoffreyrose/us-holidays).

## Features

- **Flexible Date Definitions** - Add your own dates with simple or complex logic
- **Extensions Support** - Create reusable date collections for country-specific holidays, birthdays, or any custom dates
- **Business Day Helpers** - Built-in methods for working with business days
- **Filtering** - Filter dates by custom properties with various operators
- **Laravel Integration** - Works seamlessly with Laravel via auto-discovered Facade

On its own, UsefulDates comes with no predefined dates. It provides a clean base that you can extend with your own dates and methods.

## Requirements

- PHP 8.4+
- [Carbon](http://carbon.nesbot.com/)

## Installation

```bash
composer require geoffreyrose/useful-dates
```

## Quick Start

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now());

// Add a simple date
$usefulDates->addDate(name: "Patrick Star's Birthday", date: Carbon::createFromFormat('Y-m-d', '1999-08-17'));

// Get upcoming dates
$dates = $usefulDates->getUsefulDatesInDays(30);
```

### Laravel Facade

Laravel uses Package Auto-Discovery, so no manual registration is needed:

```php
use UsefulDates\Facades\UsefulDates;

$usefulDates = UsefulDates::setDate(Carbon::now());
```


---

## Table of Contents

- [Creating Custom Dates](#creating-custom-dates)
- [Methods](#methods)
  - [Core Methods](#core-methods)
  - [Range & Navigation Methods](#range--navigation-methods)
  - [Business Day Methods](#business-day-methods)
- [UsefulDate Properties](#usefuldate-properties)
- [Extensions](#extensions)
- [Filtering](#filtering)
- [Development](#development)

---

## Creating Custom Dates

You can create custom date definitions by extending `UsefulDateAbstract`. Each custom date class must implement the `date()` method which returns the date for the current context year.

> **Important**: The `date()` method should always return dates in UTC time.

### Step 1: Create a Date Class

Create a new class that extends [`UsefulDates\Abstracts\UsefulDateAbstract`](https://github.com/geoffreyrose/useful-dates/blob/main/src/UsefulDates/Abstracts/UsefulDateAbstract.php):

```php
<?php

namespace App\Dates;

use Carbon\Carbon;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Enums\RepeatFrequency;

class AprilFools extends UsefulDateAbstract
{
    public function __construct()
    {
        $this->name = "April Fools' Day";
        $this->start_date = Carbon::createFromFormat('Y-m-d', '1582-04-01');
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', "{$this->currentDate->year}-04-01");
    }
}
```

> **Tip**: `Carbon::createFromFormat()` is faster than `Carbon::create()` in my benchmarks.

### Step 2: Register Your Date

Use the `add()` method to register your custom date class:

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now());
$usefulDates->add(\App\Dates\AprilFools::class);

$nextDates = $usefulDates->getNextUsefulDates(5);
// Returns the next 5 occurrences of April Fools' Day
```

---

## Methods

### Core Methods

#### `setDate(Carbon|DateTime|string $date): self`

Set the working date/time context (normalized to UTC). Accepts Carbon, DateTime, or a string parsable by Carbon.

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now());

// Strings and DateTime also work
$usefulDates->setDate('2025-02-01');
$usefulDates->setDate(new \DateTime('2025-03-01 08:00'));
```

#### `add(string $dateClass): self`

Register a UsefulDate by class name. The class must extend `UsefulDateAbstract`.

```php
$usefulDates->add(\App\Dates\AprilFools::class);
```

#### `addDate(string $name, Carbon $date, ...): self`

Add a simple UsefulDate without creating a class. Creates an internal definition that repeats according to the provided options.

**Parameters:**
- `$name` - Human-friendly name for the date
- `$date` - Prototype date (month/day are used for each occurrence)
- `$isRepeated` - Whether the date repeats (default: `true`)
- `$repeatFrequency` - `RepeatFrequency::YEARLY`, `MONTHLY`, `NONE`, or `CUSTOM` (default: `YEARLY`)
- `$startYear` - First year the date is considered (default: year from `$date`)

```php
$usefulDates->addDate(
    name: "Patrick Star's Birthday",
    date: Carbon::createFromFormat('Y-m-d', '1999-08-17'),
);
```

#### `addExtension(string $extensionClass, mixed $options = null): self`

Register an extension (must extend `UsefulDatesExtensionAbstract`). Adds its useful dates and any custom methods exposed by the extension.

```php
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsefulDatesUsHolidaysExtension::class);
```

#### `isUsefulDate(?Carbon $date = null): bool`

Returns `true` if any registered UsefulDate matches the given date (defaults to the current context date).

```php
$usefulDates->setDate(Carbon::now());
if ($usefulDates->isUsefulDate()) {
    // today matches a useful date
}

$someDate = Carbon::create(2025, 12, 25);
$isUseful = $usefulDates->isUsefulDate($someDate);
```

#### `getUsefulDate(?Carbon $date = null, ?array $filters = null): array`

Returns a list of matching UsefulDate objects for the given date (defaults to the current context date).

```php
$dates = $usefulDates->getUsefulDate(Carbon::create(2025, 1, 1));
foreach ($dates as $date) {
    echo $date->name . ' occurs on ' . $date->usefulDate();
}
```

> See the [Filtering](#filtering) section for filter options.

### Range & Navigation Methods

#### `getUsefulDatesInDays(int $days, ?Carbon $startDate = null, ?array $filters = null): array`

Get all matching UsefulDates occurring from `$startDate` (default: current context) through `$startDate + $days`.

```php
// Next 30 days from current context
$usefulDates->setDate(Carbon::now());
$next30 = $usefulDates->getUsefulDatesInDays(30);

// From a specific start date
$start = Carbon::create(2025, 1, 1);
$dates = $usefulDates->getUsefulDatesInDays(60, $start);
```

#### `getUsefulDatesInYears(int $years, ?array $filters = null): array`

Get all useful dates within the next N years from the current context date.

```php
$usefulDates->setDate(Carbon::now());
$twoYears = $usefulDates->getUsefulDatesInYears(2);
```

#### `getNextUsefulDates(int $number = 1, ?array $filters = null): array`

Get the next N useful dates after the current context date.

```php
$usefulDates->setDate(Carbon::now());
$next5 = $usefulDates->getNextUsefulDates(5);
```

#### `getPreviousUsefulDates(int $number = 1, ?array $filters = null): array`

Get the previous N useful dates before the current context date.

```php
$prev3 = $usefulDates->getPreviousUsefulDates(3);
```

#### `getUsefulDatesByYear(?int $year = null, ?array $filters = null): array`

Get all useful dates within a given calendar year (defaults to current context year).

```php
$byYear = $usefulDates->getUsefulDatesByYear(2025);
```

### Business Day Methods

Default business days are Monday–Friday (`[1, 2, 3, 4, 5]`). Days use Carbon's constants where `0 = Sunday` through `6 = Saturday`.

#### `isStandardBusinessDays(): bool`

Returns `true` if the configured business days are Monday–Friday.

```php
$usefulDates->setBusinessDays([1, 2, 3, 4, 5]);
$usefulDates->isStandardBusinessDays(); // true
```

#### `setBusinessDays(array $days): self`

Define which days of week are business days (0=Sun..6=Sat). Throws `InvalidDayException` for invalid values.

```php
$usefulDates->setBusinessDays([1, 2, 3, 4, 6]); // Mon-Thu + Sat
```

#### `getBusinessDays(): array`

Get the configured business days array.

```php
$days = $usefulDates->getBusinessDays(); // [1, 2, 3, 4, 5]
```

#### `isBusinessDay(?Carbon $date = null): bool`

Check if the given date (or current context date) is a business day.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // Saturday
$usefulDates->setBusinessDays([1, 2, 3, 4, 5]);
$usefulDates->isBusinessDay(); // false
```

#### `nextBusinessDay(): ?Carbon`

Get the next business day after the current context date.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // Saturday
$usefulDates->nextBusinessDay()->toDateString(); // "2025-05-12"
```

#### `prevBusinessDay(): ?Carbon`

Get the previous business day before the current context date.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // Saturday
$usefulDates->prevBusinessDay()->toDateString(); // "2025-05-09"
```

#### `todayOrPreviousBusinessDay(): Carbon`

Returns today if it is a business day, otherwise the previous business day.

```php
$usefulDates->todayOrPreviousBusinessDay()->toDateString();
```

#### `todayOrNextBusinessDay(): Carbon`

Returns today if it is a business day, otherwise the next business day.

```php
$usefulDates->todayOrNextBusinessDay()->toDateString();
```

---

## UsefulDate Properties

When creating custom date classes by extending `UsefulDateAbstract`, you have access to these properties:

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$name` | `string` | (required) | Human-friendly name for the date |
| `$additional_search_names` | `array` | `[]` | Alternative names for searching |
| `$is_repeated` | `bool` | `false` | Whether the date repeats |
| `$repeat_frequency` | `RepeatFrequency` | `NONE` | `YEARLY`, `MONTHLY`, `NONE`, or `CUSTOM` |
| `$start_date` | `?Carbon` | `null` | First date the event is considered |
| `$end_date` | `?Carbon` | `null` | Last date the event is considered |

You may also add any additonal properties to your custom dates as needed

### Instance Methods

These methods are available on each UsefulDate instance:

#### `usefulDate(): ?Carbon`

Returns the date if it should be considered for the current context (applies repeat frequency and start/end rules), otherwise `null`.

#### `daysAway(): int`

Returns the number of days from the current context to this date. Positive = future, negative = past, 0 = today.

### Complete Example

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::create(2025, 1, 1));
$usefulDates->addDate("Patrick Star's Birthday", Carbon::createFromFormat('Y-m-d', '1999-08-17'));
$usefulDates->addDate('April Fools Day', Carbon::createFromFormat('Y-m-d', '1582-04-01'));

$dates = $usefulDates->getUsefulDatesInYears(10);
foreach ($dates as $date) {
    echo $date->name . ' - ' . $date->usefulDate()->toDateString() . ' (' . $date->daysAway() . ' days away)';
}
```

## Extensions

Extensions allow you to bundle groups of UsefulDates together, optionally with custom methods.

### Available Extensions

| Extension | Description |
|-----------|-------------|
| [geoffreyrose/useful-dates-us-holidays](https://github.com/geoffreyrose/useful-dates-us-holidays) | US Holidays |

### Using an Extension

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now());
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsefulDatesUsHolidaysExtension::class);
```

### Creating an Extension

Create a class that extends [`UsefulDatesExtensionAbstract`](https://github.com/geoffreyrose/useful-dates/blob/main/src/UsefulDates/Abstracts/UsefulDatesExtensionAbstract.php):

```php
<?php

namespace App\Extensions;

use App\Dates\AprilFools;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;

class MyHolidays extends UsefulDatesExtensionAbstract
{
    public static string $name = 'My Holidays';
    public static string $description = 'Custom holiday collection';
    public static bool $hasMethods = true;

    // Return array of UsefulDate class names
    public static function usefulDates(): array
    {
        return [
            AprilFools::class,
        ];
    }
    
    // Optional: Add custom methods
    public function methods(): array
    {
        return [
            'isDecember' => [$this, 'isDecember'],
        ];
    }

    public function isDecember(): bool
    {
        return $this->usefulDates->date->month === 12;
    }
}
```

Using the extension with custom methods:

```php
$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now()->addMonths(2));
$usefulDates->addExtension(\App\Extensions\MyHolidays::class);

if ($usefulDates->isDecember()) {
    echo "It's December!";
}
```

### IDE Support

For better IDE autocompletion with extension methods, create a wrapper class:

```php
<?php

namespace App;

use UsefulDates\UsefulDates;
use UsefulDatesUsHolidays\UsefulDatesUsHolidaysExtension;
use Carbon\Carbon;

/**
 * @mixin UsefulDatesUsHolidaysExtension
 */
class MyUsefulDates extends UsefulDates
{
    public function init(Carbon $date): self
    {
        $this->setDate($date);
        $this->addExtension(UsefulDatesUsHolidaysExtension::class);
        return $this;
    }
}
```

```php
$dates = new \App\MyUsefulDates();
$dates->init(Carbon::now());
```


## Filtering

Filter useful dates by custom properties using the `$filters` parameter available on most retrieval methods.

### Filter Structure

Each filter is an array with three keys:

| Key | Description |
|-----|-------------|
| `property` | The property name on the UsefulDate class |
| `operator` | Comparison operator: `>`, `<`, `>=`, `<=`, `=`, `!=` |
| `value` | The value to compare against |

### Example

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$usefulDates = new UsefulDates();
$usefulDates->setDate(Carbon::now());
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsHolidaysExtension::class);

// Get federal holidays established before 1900
$federalHolidays = $usefulDates->getUsefulDatesInDays(100, filters: [
    [
        'property' => 'is_federal_holiday',
        'operator' => '=',
        'value' => true,
    ],
    [
        'property' => 'federal_holiday_start_year',
        'operator' => '<=',
        'value' => 1900,
    ],
]);
```

Multiple filters are combined with AND logic (all conditions must match).

---

## Development

### Linting

```bash
./vendor/bin/pint
```

### Testing

```bash
# Run tests
./vendor/bin/pest

# Run tests with coverage
./vendor/bin/pest --coverage-html coverage

# With Laravel Herd
herd coverage ./vendor/bin/pest --coverage-html coverage
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.
