<?php

namespace App\Notifications;

use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $body;
    public Reservation $reservation;
    public bool $deleted;
    public string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, Reservation $reservation, string $type = 'info', bool $deleted = false)
    {
        $this->title = $title;
        $this->body = $body;
        $this->reservation = $reservation;
        $this->type = $type;
        $this->deleted = $deleted;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->from($this->reservation->tenant->email ?? config('mail.from.address'))
            ->greeting(__('Hello, :name!', ['name' => $notifiable->name]))
            ->subject($this->title)
            ->line($this->body);

        if (!$this->deleted) {
            $mailMessage->action(
                __('View Reservation'),
                url("/app/{$this->reservation->tenant->slug}/reservations/{$this->reservation->id}")
            );
        } else {
            $mailMessage->action(
                'Go to Dashboard',
                url("/app/{$this->reservation->tenant->slug}")
            );
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'reservation' => $this->reservation,
            'type' => $this->type,
            'deleted' => $this->deleted,
        ];
    }

    /**
     * Send a Filament notification.
     *
     * @param mixed $notifiable
     * @return void
     */
    public function toFilament($notifiable)
    {
        $notification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->body);

        $typeMethod = $this->type;
        if (method_exists($notification, $typeMethod)) {
            call_user_func([$notification, $typeMethod]);
        }

        if (!$this->deleted) {
            $notification->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label(__('View Reservation'))
                    ->url(url("/app/{$this->reservation->tenant->slug}/reservations/{$this->reservation->id}"))
                    ->button()
                    ->color('primary')
            ]);
        }

        $notification->sendToDatabase($notifiable);
    }
}
