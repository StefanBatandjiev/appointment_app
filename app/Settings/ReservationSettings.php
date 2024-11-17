<?php

namespace App\Settings;

use Filament\Facades\Filament;
use Spatie\LaravelSettings\Settings;

class ReservationSettings extends Settings
{

    public int $reservationMaxDuration;

    public int $reservationTimeInterval;

    public int $breakMaxDuration;

    public int $breakTimeInterval;

    public static function group(): string
    {
        return 'reservations_' . Filament::getTenant()->id;
    }
}
