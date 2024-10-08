<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendReservationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for upcoming reservations or finished reservations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->notifyAboutUpcomingReservations();

        $this->notifyAboutFinishedReservations();
    }

    protected function notifyAboutUpcomingReservations()
    {
        Reservation::query()
            ->where('start_time', '>=', now()->addMinutes(15))
            ->where('start_time', '<=', now()->addMinutes(16))
            ->cursor()
            ->each(function ($reservation) {

                Notification::make()
                    ->title("Reservation on " . $reservation->machine->name  ." starts in 15 minutes!")
                    ->body("Reservation scheduled at: " . $reservation->start_time)
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->color('warning')
                            ->url('/admin/reservations/' . $reservation->id),
                        Action::make('edit')
                            ->button()
                            ->color('warning')
                            ->url('/admin/reservations/' . $reservation->id . '/edit')
                    ])
                    ->warning()
                    ->sendToDatabase($reservation->assigned_user);

                event(new DatabaseNotificationsSent($reservation->assigned_user));
            });
    }

    protected function notifyAboutFinishedReservations()
    {
        Reservation::query()
            ->where('end_time', '<=', now()->addMinute())
            ->where('end_time', '>=', now())
            ->where('status', '=', ReservationStatus::SCHEDULED)
            ->cursor()
            ->each(function ($reservation) {

                Notification::make()
                    ->title("Reservation on " . $reservation->machine->name  ." is pending to be finished!")
                    ->body("Reservation ended at: " . $reservation->end_time)
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url('/admin/reservations/' . $reservation->id)
                            ->close()
                    ])
                    ->info()
                    ->sendToDatabase($reservation->assigned_user);

                event(new DatabaseNotificationsSent($reservation->assigned_user));
            });
    }
}
