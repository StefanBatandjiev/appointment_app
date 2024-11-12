<?php

namespace App\Filament\App\Clusters\Pages;

use App\Filament\App\Clusters\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class CalendarSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $cluster = Settings::class;

    public static function getNavigationLabel(): string
    {
        return __('Calendar Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Calendar Settings');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Settings'),
            __('Calendar Settings')
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('slotMinTime')->label('Working Hours Start')->required(),
                TextInput::make('slotMaxTime')->label('Working Hours End')->required(),
                TextInput::make('slotDuration')->label('Calendar Slot Duration')->required(),
            ]);
    }
}
