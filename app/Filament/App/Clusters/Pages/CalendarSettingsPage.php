<?php

namespace App\Filament\App\Clusters\Pages;

use App\Filament\App\Clusters\Settings;
use App\Settings\CalendarSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class CalendarSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 1;
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

    protected function getTenantSettings()
    {
        return app(CalendarSettings::class);
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
                TimePicker::make('slotMinTime')
                    ->label(__('Working Hours Start'))
                    ->required()
                    ->default($tenantSettings->slotMinTime),
                TimePicker::make('slotMaxTime')
                    ->label(__('Working Hours End'))
                    ->required()
                    ->default($tenantSettings->slotMaxTime),
                TimePicker::make('slotDuration')
                    ->label(__('Calendar Slot Duration'))
                    ->required()
                    ->default($tenantSettings->slotDuration),
                ColorPicker::make('pending_finish_color')
                    ->label(__('Pending Finish Status Color'))
                    ->required()
                    ->default($tenantSettings->pending_finish_color),
                ColorPicker::make('finished_color')
                    ->label(__('Finish Status Color'))
                    ->required()
                    ->default($tenantSettings->finished_color),
                ColorPicker::make('finished_break_color')
                    ->label(__('Finished Break Color'))
                    ->required()
                    ->default($tenantSettings->finished_break_color),
                ColorPicker::make('break_color')
                    ->label(__('Break Color'))
                    ->required()
                    ->default($tenantSettings->break_color),
            ]);
    }

    public function save(): void
    {
        $tenantSettings = $this->getTenantSettings();

        $tenantSettings->slotMinTime = $this->form->getState()['slotMinTime'];
        $tenantSettings->slotMaxTime = $this->form->getState()['slotMaxTime'];
        $tenantSettings->slotDuration = $this->form->getState()['slotDuration'];
        $tenantSettings->pending_finish_color = $this->form->getState()['pending_finish_color'];
        $tenantSettings->finished_color = $this->form->getState()['finished_color'];
        $tenantSettings->finished_break_color = $this->form->getState()['finished_break_color'];
        $tenantSettings->break_color = $this->form->getState()['break_color'];

        $tenantSettings->save();

        Notification::make()
            ->title(__('Calendar Settings saved successfully'))
            ->success()
            ->send();
    }
}
