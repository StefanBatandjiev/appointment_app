<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CalendarSettings extends Settings
{

    public string $slotMinTime;
    public string $slotMaxTime;
    public string $slotDuration;

    public static function group(): string
    {
        return 'calendar';
    }
}
