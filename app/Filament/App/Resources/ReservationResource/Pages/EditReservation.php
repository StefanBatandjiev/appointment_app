<?php

namespace App\Filament\App\Resources\ReservationResource\Pages;

use App\Filament\App\Resources\ReservationResource;
use App\Services\ReservationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Edit Reservation');
    }

    public static function getNavigationLabel(): string
    {
        return __('Edit Reservation');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ReservationService::editAction($data);
    }
}
