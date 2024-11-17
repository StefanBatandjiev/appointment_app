<?php

namespace App\Settings;

use Filament\Facades\Filament;
use Spatie\LaravelSettings\Settings;

class CalendarSettings extends Settings
{

    public string $slotMinTime;
    public string $slotMaxTime;
    public string $slotDuration;

    public string $pending_finish_color;

    public string $finished_color;
    public string $finished_break_color;
    public string $break_color;

    public static function group(): string
    {
        return 'calendar_' . Filament::getTenant()->id;
    }
}
