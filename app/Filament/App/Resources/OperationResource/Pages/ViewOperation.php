<?php

namespace App\Filament\App\Resources\OperationResource\Pages;

use App\Filament\App\Resources\OperationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOperation extends ViewRecord
{
    protected static string $resource = OperationResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('View Operation');
    }

    public static function getNavigationLabel(): string
    {
        return __('View Operation');
    }
}
