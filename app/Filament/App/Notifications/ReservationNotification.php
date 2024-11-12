<?php

namespace App\Filament\App\Notifications;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class ReservationNotification
{
    public static function make(User $user, string $title, string $body, int $reservationId, string $status = 'info'): Notification
    {
        $notification =  Notification::make()
            ->title($title)
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label(__('View'))
                    ->button()
                    ->url('/admin/reservations/' . $reservationId)
            ]);

        if ($status === 'success') {
            $notification->success();
        } else if ($status === 'warning') {
            $notification->warning();
        } else if ($status === 'danger') {
            $notification->danger();
        } else {
            $notification->info();
        }

        $notification->sendToDatabase($user);

        return $notification;
    }

}
