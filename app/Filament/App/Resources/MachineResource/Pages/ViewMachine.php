<?php

namespace App\Filament\App\Resources\MachineResource\Pages;

use App\Filament\App\Resources\MachineResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewMachine extends ViewRecord
{
    protected static string $resource = MachineResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('View Machine');
    }

    public static function getNavigationLabel(): string
    {
        return __('View Machine');
    }

    protected function getFooterWidgets(): array
    {
        return [
          \App\Filament\App\Resources\MachineResource\Widgets\MachineReservations::class,
        ];
    }
}
