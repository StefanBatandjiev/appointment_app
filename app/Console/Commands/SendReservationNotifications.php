<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SendReservationNotifications extends Command
{
    protected SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }
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
            ->where('status', '=', ReservationStatus::SCHEDULED)
            ->cursor()
            ->each(function ($reservation) {

                $notification = new \App\Notifications\ReservationNotification(
                    __("Reservation on ") . $reservation->machine->name  . __(" starts in 15 minutes!"),
                    __("Reservation starts at ") . Carbon::parse($reservation->start_time)->translatedFormat('H:i')
                    . __(', operations that need to be done: ') . $reservation->operations->pluck('name')->implode(', ') . '.',
                    $reservation,
                    'warning'
                );

                if ($this->settings->getPropertyPayload('notifications_' . $reservation->tenant->id, 'upcomingReservationNotificationToMail')) {
                    $reservation->assigned_user->notify($notification);
                }

                if ($this->settings->getPropertyPayload('notifications_' . $reservation->tenant->id, 'upcomingReservationNotification')) {
                    $notification->toFilament($reservation->assigned_user);
                }
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

                $notification = new \App\Notifications\ReservationNotification(
                    __("Reservation on ") . $reservation->machine->name  . __(" is pending to be finished!"),
                    __("Reservation ended at: ") . Carbon::parse($reservation->end_time)->translatedFormat('D, d M Y H:i'),
                    $reservation,
                    'info'
                );

                if ($this->settings->getPropertyPayload('notifications_' . $reservation->tenant->id, 'finishedReservationNotificationToMail')) {
                    $reservation->assigned_user->notify($notification);
                }
                if ($this->settings->getPropertyPayload('notifications_' . $reservation->tenant->id, 'finishedReservationNotification')) {
                    $notification->toFilament($reservation->assigned_user);
                }
            });
    }
}
