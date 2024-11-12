<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReservationSettings extends Settings
{

    public static function group(): string
    {
        return 'reservation';
    }
}
