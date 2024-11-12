<?php

namespace App\Filament\App\Resources\ReservationResource\Pages;

use App\Filament\App\Resources\ReservationResource;
use App\Services\ReservationService;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ReservationService::createAction($data);
    }
}
