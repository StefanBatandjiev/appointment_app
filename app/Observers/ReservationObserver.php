<?php

namespace App\Observers;

use App\Enums\ReservationStatus;
use App\Filament\App\Notifications\ReservationNotification;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Events\DatabaseNotificationsSent;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        if ($reservation->assigned_user) {
            ReservationNotification::make(
                $reservation->assigned_user,
                __('You have been assigned to a reservation!'),
                __('Reservation scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation->id,
                'success'
            );

            event(new DatabaseNotificationsSent($reservation->assigned_user));
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        if ($reservation->wasChanged('assigned_user_id')) {
            $previousAssignedUserId = $reservation->getOriginal('assigned_user_id');

            if ($previousAssignedUserId) {
                $previousAssignedUser = User::query()->find($previousAssignedUserId);

                // Notify the previous assigned user, if any
                if ($previousAssignedUser) {
                    ReservationNotification::make(
                        $previousAssignedUser,
                        __('Reservation Reassigned!'),
                        __('Reservation scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i') . __(' has been reassigned.'),
                        $reservation->id,
                        'warning'
                    );

                    event(new DatabaseNotificationsSent($previousAssignedUser));
                }
            }

            // Notify the new assigned user
            $newAssignedUser = User::query()->findOrFail($reservation->assigned_user_id);
            ReservationNotification::make(
                $newAssignedUser,
                __('You have been assigned a reservation!'),
                __('Reservation scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation->id,
                'success'
            );

            event(new DatabaseNotificationsSent($newAssignedUser));
        }

        if ($reservation->wasChanged('status')) {
            if ($reservation->status === ReservationStatus::CANCELED) {

                ReservationNotification::make(
                    $reservation->assigned_user,
                    __('Reservation has been canceled!'),
                    __('Reservation was scheduled at: ') . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                    $reservation->id,
                    'danger'
                );

                event(new DatabaseNotificationsSent($reservation->assigned_user));
            }
        }

        if ($reservation->wasChanged('start_time')) {
            ReservationNotification::make(
                $reservation->assigned_user,
                __('Reservation has been rescheduled!'),
                __('Reservation is rescheduled from: ')
                . Carbon::parse($reservation->getOriginal('start_time'))->translatedFormat('D, d M Y H:i')
                . __(" to ") . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i'),
                $reservation->id
            );
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        $user = User::query()->findOrFail($reservation->assigned_user_id);
        $client = Client::query()->findOrFail($reservation->client_id);

        ReservationNotification::make(
        $user,
        __('Reservation has been deleted!'),
        __('Reservation for ') .  $client->name . __(' scheduled at: ')
        . Carbon::parse($reservation->start_time)->translatedFormat('D, d M Y H:i')
        . __(' was deleted by ') . auth()->user()->name,
        $reservation->id
    );
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
