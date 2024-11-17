<?php

namespace App\Settings;

use Filament\Facades\Filament;
use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $assignedNotification;
    public bool $assignedNotificationToMail;
    public bool $reAssignedNotification;
    public bool $reAssignedNotificationToMail;
    public bool $canceledReservationNotification;
    public bool $canceledReservationNotificationToMail;
    public bool $rescheduledReservationNotification;
    public bool $rescheduledReservationNotificationToMail;

    public bool $deletedReservationNotification;
    public bool $deletedReservationNotificationToMail;

    public bool $upcomingReservationNotification;
    public bool $upcomingReservationNotificationToMail;

    public bool $finishedReservationNotification;
    public bool $finishedReservationNotificationToMail;



    public static function group(): string
    {
        return 'notifications_' . Filament::getTenant()->id;
    }
}
