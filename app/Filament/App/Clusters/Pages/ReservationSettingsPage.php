<?php

namespace App\Filament\App\Clusters\Pages;

use App\Filament\App\Clusters\Settings;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ReservationSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $cluster = Settings::class;

    public static function getNavigationLabel(): string
    {
        return __('Reservation Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Reservation Settings');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Settings'),
            __('Reservation Settings')
        ];
    }

    public function form(Form $form): Form
    {
        return $form;
    }
}
