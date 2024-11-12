<?php

namespace App\Filament\App\Resources\MachineResource\Pages;

use App\Filament\App\Resources\MachineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMachine extends EditRecord
{
    protected static string $resource = MachineResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Edit Machine');
    }

    public static function getNavigationLabel(): string
    {
        return __('Edit Machine');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
