<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (isset($data['start']) && isset($data['duration'])) {
            $date = Carbon::parse($data['date']);

            [$hour, $minute] = explode(':', $data['start']);

            $startTime = $date->setTime((int)$hour, (int)$minute);
            $data['start_time'] = $startTime;

            $duration = (int) $data['duration'];

            $data['end_time'] = (clone $startTime)->addMinutes($duration);

            if (isset($data['break'])) {
                $data['break_time'] = (clone $data['end_time'])->addMinutes((int)$data['break']);
            }
        }

        return $data;
    }
}
