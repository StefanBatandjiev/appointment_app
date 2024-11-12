<?php

namespace App\Filament\App\Resources\OperationResource\Pages;

use App\Filament\App\Resources\OperationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperation extends EditRecord
{
    protected static string $resource = OperationResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Edit Operation');
    }

    public static function getNavigationLabel(): string
    {
        return __('Edit Operation');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
