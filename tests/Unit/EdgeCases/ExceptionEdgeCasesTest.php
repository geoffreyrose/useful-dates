<?php

use Carbon\Carbon;
use UsefulDates\Exceptions\InvalidDateException;
use UsefulDates\Exceptions\InvalidDayException;
use UsefulDates\Exceptions\InvalidExtensionException;
use UsefulDates\Exceptions\InvalidUsefulDateException;
use UsefulDates\UsefulDates;

it('InvalidDateException contains correct message for string type', function (): void {
    $ud = new UsefulDates;

    try {
        $ud->setDate('invalid date string');
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidDateException $e) {
        expect($e->getMessage())->toContain('string');
    }
});

it('InvalidDayException contains correct message for invalid integer', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setBusinessDays([10]);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidDayException $e) {
        expect($e->getMessage())->toContain('10');
        expect($e->getMessage())->toContain('integer');
    }
});

it('InvalidDayException contains correct message for string type', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setBusinessDays(['Monday']);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidDayException $e) {
        expect($e->getMessage())->toContain('Monday');
        expect($e->getMessage())->toContain('string');
    }
});

it('InvalidDayException contains correct message for float type', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setBusinessDays([1.5]);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidDayException $e) {
        expect($e->getMessage())->toContain('double');
    }
});

it('InvalidDayException contains correct message for null type', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setBusinessDays([null]);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidDayException $e) {
        expect($e->getMessage())->toContain('NULL');
    }
});

it('InvalidUsefulDateException is thrown with correct type', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->add('DateTime');
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidUsefulDateException $e) {
        expect($e)->toBeInstanceOf(InvalidUsefulDateException::class);
        expect($e)->toBeInstanceOf(\RuntimeException::class);
    }
});

it('InvalidExtensionException is thrown with correct type', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->addExtension('DateTime');
        expect(false)->toBeTrue(); // Should not reach here
    } catch (InvalidExtensionException $e) {
        expect($e)->toBeInstanceOf(InvalidExtensionException::class);
        expect($e)->toBeInstanceOf(\RuntimeException::class);
    }
});

it('exceptions inherit from RuntimeException', function (): void {
    expect(new InvalidDateException('test'))->toBeInstanceOf(\RuntimeException::class);
    expect(new InvalidDayException(10))->toBeInstanceOf(\RuntimeException::class);
    expect(new InvalidUsefulDateException)->toBeInstanceOf(\RuntimeException::class);
    expect(new InvalidExtensionException)->toBeInstanceOf(\RuntimeException::class);
});

it('InvalidDayException handles negative number in message', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setBusinessDays([-5]);
        expect(false)->toBeTrue();
    } catch (InvalidDayException $e) {
        expect($e->getMessage())->toContain('-5');
    }
});

it('InvalidDateException handles array type', function (): void {
    $ud = new UsefulDates;

    try {
        $ud->setDate([1, 2, 3]);
        expect(false)->toBeTrue();
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('array');
    }
});

it('InvalidDateException handles null type', function (): void {
    $ud = new UsefulDates;

    try {
        $ud->setDate(null);
        expect(false)->toBeTrue();
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('null');
    }
});

it('InvalidDateException handles stdClass type', function (): void {
    $ud = new UsefulDates;

    try {
        $ud->setDate(new stdClass);
        expect(false)->toBeTrue();
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('stdClass');
    }
});
