<?php

namespace App\Filament\App\Resources\ReservationResource\Pages;

use App\Filament\App\Resources\ReservationResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('View Reservation');
    }

    public static function getNavigationLabel(): string
    {
        return __('View Reservation');
    }
}
