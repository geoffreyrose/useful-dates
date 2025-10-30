<?php

namespace UsefulDates\Enums;

enum RepeatFrequency: string
{
    // case DAILY = 'daily';
    // case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case NONE = 'none';
    case CUSTOM = 'custom';
}
