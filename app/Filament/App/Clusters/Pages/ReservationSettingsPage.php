<?php

namespace App\Filament\App\Clusters\Pages;

use App\Filament\App\Clusters\Settings;
use App\Settings\ReservationSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ReservationSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 2;
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

    protected function getTenantSettings()
    {
        return app(ReservationSettings::class);
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(__('Save changes'))
        ];
    }

    public function form(Form $form): Form
    {
        $tenantSettings = $this->getTenantSettings();

        return $form
            ->schema([
                TextInput::make('reservationMaxDuration')
                    ->label(__('Maximum Duration for Reservation'))
                    ->numeric()
                    ->required()
                    ->default($tenantSettings->reservationMaxDuration),
                TextInput::make('reservationTimeInterval')
                    ->label(__('Time Interval for Reservations'))
                    ->numeric()
                    ->required()
                    ->default($tenantSettings->reservationTimeInterval),
                TextInput::make('breakMaxDuration')
                    ->label(__('Maximum Duration for Break'))
                    ->numeric()
                    ->required()
                    ->default($tenantSettings->breakMaxDuration),
                TextInput::make('breakTimeInterval')
                    ->label(__('Time Interval for Breaks'))
                    ->numeric()
                    ->required()
                    ->default($tenantSettings->breakTimeInterval),
            ]);
    }

    public function save(): void
    {
        $tenantSettings = $this->getTenantSettings();

        $tenantSettings->reservationMaxDuration = $this->form->getState()['reservationMaxDuration'];
        $tenantSettings->reservationTimeInterval = $this->form->getState()['reservationTimeInterval'];
        $tenantSettings->breakMaxDuration = $this->form->getState()['breakMaxDuration'];
        $tenantSettings->breakTimeInterval = $this->form->getState()['breakTimeInterval'];

        $tenantSettings->save();

        Notification::make()
            ->title(__('Reservation Settings saved successfully'))
            ->success()
            ->send();
    }
}
