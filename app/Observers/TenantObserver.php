<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Settings\CalendarSettings;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class TenantObserver
{
    protected SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'slotMinTime',
            '08:00:00',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'slotMaxTime',
            '20:00:00',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'slotDuration',
            '00:15:00',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'pending_finish_color',
            '#e1e2e3',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'finished_color',
            '#bbcafc',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'finished_break_color',
            '#f7d0e0',
        );

        $this->settings->createProperty(
            'calendar_' . $tenant->id,
            'break_color',
            '#FF7F7F',
        );

        $this->settings->createProperty(
            'reservations_' . $tenant->id,
            'reservationMaxDuration',
            180,
        );

        $this->settings->createProperty(
            'reservations_' . $tenant->id,
            'reservationTimeInterval',
            15,
        );

        $this->settings->createProperty(
            'reservations_' . $tenant->id,
            'breakMaxDuration',
            180,
        );

        $this->settings->createProperty(
            'reservations_' . $tenant->id,
            'breakTimeInterval',
            5,
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'assignedNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'assignedNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'reAssignedNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'reAssignedNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'canceledReservationNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'canceledReservationNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'rescheduledReservationNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'rescheduledReservationNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'deletedReservationNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'deletedReservationNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'upcomingReservationNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'upcomingReservationNotificationToMail',
            false
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'finishedReservationNotification',
            true
        );

        $this->settings->createProperty(
            'notifications_' . $tenant->id,
            'finishedReservationNotificationToMail',
            false
        );
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        // Delete notification settings
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'assignedNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'assignedNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'reAssignedNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'reAssignedNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'canceledReservationNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'canceledReservationNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'rescheduledReservationNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'rescheduledReservationNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'deletedReservationNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'deletedReservationNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'upcomingReservationNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'upcomingReservationNotificationToMail');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'finishedReservationNotification');
        $this->settings->deleteProperty('notifications_' . $tenant->id, 'finishedReservationNotificationToMail');

        // Delete calendar settings
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'slotMinTime');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'slotMaxTime');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'slotDuration');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'pending_finish_color');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'finished_color');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'finished_break_color');
        $this->settings->deleteProperty('calendar_' . $tenant->id, 'break_color');

        // Delete reservation settings
        $this->settings->deleteProperty('reservations_' . $tenant->id, 'reservationMaxDuration');
        $this->settings->deleteProperty('reservations_' . $tenant->id, 'reservationTimeInterval');
        $this->settings->deleteProperty('reservations_' . $tenant->id, 'breakMaxDuration');
        $this->settings->deleteProperty('reservations_' . $tenant->id, 'breakTimeInterval');
    }


    /**
     * Handle the Tenant "restored" event.
     */
    public function restored(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "force deleted" event.
     */
    public function forceDeleted(Tenant $tenant): void
    {
        //
    }
}
