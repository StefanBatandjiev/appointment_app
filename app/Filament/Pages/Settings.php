<?php

namespace App\Filament\Pages;

use App\Settings\CalendarSettings;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class Settings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = CalendarSettings::class;

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
