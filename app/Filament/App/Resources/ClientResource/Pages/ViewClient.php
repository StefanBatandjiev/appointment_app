<?php

namespace App\Filament\App\Resources\ClientResource\Pages;

use App\Filament\App\Resources\ClientResource;
use App\Models\Client;
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

    public static function getNavigationLabel(): string
    {
        return __('View Client');
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClientResource\Widgets\StatsOverviewClientReservations::class,
            ClientResource\Widgets\ClientReservations::class,
        ];
    }
}
