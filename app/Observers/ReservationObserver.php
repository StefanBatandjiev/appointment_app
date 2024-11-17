<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Filament\App\Notifications\ReservationNotification;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use App\Settings\NotificationSettings;
use Carbon\Carbon;
use Filament\Facades\Filament;

class ReservationObserver
{
    protected NotificationSettings $notificationSettings;

    public function __construct()
    {
        $this->notificationSettings = app(NotificationSettings::class);
    }

    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        if ($reservation->assigned_user) {
            $notification = new \App\Notifications\ReservationNotification(
                __('You have been assigned to a reservation!'),
                __('Reservation scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation,
                'success'
            );
            if ($this->notificationSettings->assignedNotificationToMail) {
                $reservation->assigned_user->notify($notification);

            }
            if ($this->notificationSettings->assignedNotification) {
                $notification->toFilament($reservation->assigned_user);
            }
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        $slug = Filament::getTenant()->slug;

        if ($reservation->wasChanged('assigned_user_id')) {
            $previousAssignedUserId = $reservation->getOriginal('assigned_user_id');
            $newAssignedUser = User::query()->findOrFail($reservation->assigned_user_id);

            if ($previousAssignedUserId) {
                $previousAssignedUser = User::query()->find($previousAssignedUserId);

                // Notify the previous assigned user, if any
                if ($previousAssignedUser) {
                    $notification = new \App\Notifications\ReservationNotification(
                        __('Reservation Reassigned!'),
                        __('Reservation scheduled at ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i')
                        . __(' has been reassigned to ') . $newAssignedUser->name,
                        $reservation,
                        'warning'
                    );

                    if ($this->notificationSettings->reAssignedNotificationToMail) {
                        $previousAssignedUser->notify($notification);
                    }
                    if ($this->notificationSettings->reAssignedNotification) {
                        $notification->toFilament($previousAssignedUser);
                    }
                }
            }

            // Notify the new assigned user
            $notification = new \App\Notifications\ReservationNotification(
                __('You have been assigned to a reservation!'),
                __('Reservation scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation,
                'warning'
            );

            if ($this->notificationSettings->assignedNotificationToMail) {
                $newAssignedUser->notify($notification);
            }
            if ($this->notificationSettings->assignedNotification) {
                $notification->toFilament($newAssignedUser);
            }
        }

        if ($reservation->wasChanged('status')) {
            if ($reservation->status === ReservationStatus::CANCELED) {

                $notification = new \App\Notifications\ReservationNotification(
                    __('Reservation has been canceled!'),
                    __('Reservation was scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                    $reservation,
                    'danger'
                );
                if ($this->notificationSettings->canceledReservationNotificationToMail) {
                    $reservation->assigned_user->notify($notification);
                }
                if ($this->notificationSettings->canceledReservationNotification) {
                    $notification->toFilament($reservation->assigned_user);
                }
            }
        }

        if ($reservation->wasChanged('start_time')) {
            $notification = new \App\Notifications\ReservationNotification(
                __('Reservation has been rescheduled!'),
                __('Reservation is rescheduled from: ')
                . Carbon::parse($reservation->getOriginal('start_time'))->translatedFormat('D, d M Y H:i')
                . __(" to ") . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation,
                'warning'
            );
            if ($this->notificationSettings->rescheduledReservationNotificationToMail) {
                $reservation->assigned_user->notify($notification);
            }
            if ($this->notificationSettings->rescheduledReservationNotification) {
                $notification->toFilament($reservation->assigned_user);
            }
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        if ($reservation->getReservationStatusAttribute() === ReservationStatus::SCHEDULED) {
            $user = User::query()->findOrFail($reservation->assigned_user_id);
            $client = Client::query()->findOrFail($reservation->client_id);

            $notification = new \App\Notifications\ReservationNotification(
                __('Reservation has been deleted!'),
                __('Reservation for ') . $client->name . __(' scheduled at: ')
                . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i')
                . __(' was deleted by ') . auth()->user()->name,
                $reservation,
                'danger',
                true
            );

            if ($this->notificationSettings->deletedReservationNotificationToMail) {
                $user->notify($notification);
            }
            if ($this->notificationSettings->deletedReservationNotification) {
                $notification->toFilament($user);
            }
        }
    }

    /**
     * Handle the Reservation "restored" event.
     */
    public function restored(Reservation $reservation): void
    {
        //
    }

    /**
     * Handle the Reservation "force deleted" event.
     */
    public function forceDeleted(Reservation $reservation): void
    {
        //
    }
}
