<?php

namespace App\Filament\App\Clusters\Pages;

use App\Filament\App\Clusters\Settings;
use App\Settings\NotificationSettings;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class NotificationSettingsPage extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?int $navigationSort = 3;
    protected static ?string $cluster = Settings::class;

    public static function getNavigationLabel(): string
    {
        return __('Notifications Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Notifications Settings');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Settings'),
            __('Notifications Settings')
        ];
    }

    protected function getTenantSettings()
    {
        return app(NotificationSettings::class);
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
                // Assigned Notification
                Toggle::make('assignedNotification')
                    ->label(__('Assigned User Notification'))
                    ->default($tenantSettings->assignedNotification)
                    ->required(),

                // Assigned Notification to Mail
                Toggle::make('assignedNotificationToMail')
                    ->label(__('Assigned User Notification to Mail'))
                    ->default($tenantSettings->assignedNotificationToMail)
                    ->required(),

                // Re-assigned Notification
                Toggle::make('reAssignedNotification')
                    ->label(__('Re-Assigned User Notification'))
                    ->default($tenantSettings->reAssignedNotification)
                    ->required(),

                // Re-assigned Notification to Mail
                Toggle::make('reAssignedNotificationToMail')
                    ->label(__('Re-Assigned User Notification to Mail'))
                    ->default($tenantSettings->reAssignedNotificationToMail)
                    ->required(),

                // Canceled Reservation Notification
                Toggle::make('canceledReservationNotification')
                    ->label(__('Canceled Reservation Notification'))
                    ->default($tenantSettings->canceledReservationNotification)
                    ->required(),

                // Canceled Reservation Notification to Mail
                Toggle::make('canceledReservationNotificationToMail')
                    ->label(__('Canceled Reservation Notification to Mail'))
                    ->default($tenantSettings->canceledReservationNotificationToMail)
                    ->required(),

                // Rescheduled Reservation Notification
                Toggle::make('rescheduledReservationNotification')
                    ->label(__('Rescheduled Reservation Notification'))
                    ->default($tenantSettings->rescheduledReservationNotification)
                    ->required(),

                // Rescheduled Reservation Notification to Mail
                Toggle::make('rescheduledReservationNotificationToMail')
                    ->label(__('Rescheduled Reservation Notification to Mail'))
                    ->default($tenantSettings->rescheduledReservationNotificationToMail)
                    ->required(),

                // Deleted Reservation Notification
                Toggle::make('deletedReservationNotification')
                    ->label(__('Deleted Reservation Notification'))
                    ->default($tenantSettings->deletedReservationNotification)
                    ->required(),

                // Deleted Reservation Notification to Mail
                Toggle::make('deletedReservationNotificationToMail')
                    ->label(__('Deleted Reservation Notification to Mail'))
                    ->default($tenantSettings->deletedReservationNotificationToMail)
                    ->required(),

                // Upcoming Reservation Notification
                Toggle::make('upcomingReservationNotification')
                    ->label(__('Upcoming Reservation Notification'))
                    ->default($tenantSettings->upcomingReservationNotification)
                    ->required(),

                // Upcoming Reservation Notification to Mail
                Toggle::make('upcomingReservationNotificationToMail')
                    ->label(__('Upcoming Reservation Notification to Mail'))
                    ->default($tenantSettings->upcomingReservationNotificationToMail)
                    ->required(),

                // Finished Reservation Notification
                Toggle::make('finishedReservationNotification')
                    ->label(__('Finished Reservation Notification'))
                    ->default($tenantSettings->finishedReservationNotification)
                    ->required(),

                // Finished Reservation Notification to Mail
                Toggle::make('finishedReservationNotificationToMail')
                    ->label(__('Finished Reservation Notification to Mail'))
                    ->default($tenantSettings->finishedReservationNotificationToMail)
                    ->required(),
            ]);
    }

    public function save(): void
    {
        $tenantSettings = $this->getTenantSettings();

        $tenantSettings->assignedNotification = $this->form->getState()['assignedNotification'];
        $tenantSettings->assignedNotificationToMail = $this->form->getState()['assignedNotificationToMail'];
        $tenantSettings->reAssignedNotification = $this->form->getState()['reAssignedNotification'];
        $tenantSettings->reAssignedNotificationToMail = $this->form->getState()['reAssignedNotificationToMail'];
        $tenantSettings->canceledReservationNotification = $this->form->getState()['canceledReservationNotification'];
        $tenantSettings->canceledReservationNotificationToMail = $this->form->getState()['canceledReservationNotificationToMail'];
        $tenantSettings->rescheduledReservationNotification = $this->form->getState()['rescheduledReservationNotification'];
        $tenantSettings->rescheduledReservationNotificationToMail = $this->form->getState()['rescheduledReservationNotificationToMail'];
        $tenantSettings->deletedReservationNotification = $this->form->getState()['deletedReservationNotification'];
        $tenantSettings->deletedReservationNotificationToMail = $this->form->getState()['deletedReservationNotificationToMail'];
        $tenantSettings->upcomingReservationNotification = $this->form->getState()['upcomingReservationNotification'];
        $tenantSettings->upcomingReservationNotificationToMail = $this->form->getState()['upcomingReservationNotificationToMail'];
        $tenantSettings->finishedReservationNotification = $this->form->getState()['finishedReservationNotification'];
        $tenantSettings->finishedReservationNotificationToMail = $this->form->getState()['finishedReservationNotificationToMail'];

        $tenantSettings->save();

        Notification::make()
            ->title(__('Notifications Settings saved successfully'))
            ->success()
            ->send();
    }
}
