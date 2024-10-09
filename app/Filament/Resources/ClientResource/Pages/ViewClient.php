<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        /** @var Client */
        $record = $this->getRecord();

        return $record->name;
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClientResource\Widgets\StatsOverviewClientReservations::class,
            ClientResource\Widgets\ClientReservations::class,
        ];
    }
}
