<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use App\Models\Reservation;
use App\Services\ReservationService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Guava\Calendar\Actions\CreateAction;
use Guava\Calendar\Actions\EditAction;
use Guava\Calendar\Actions\ViewAction;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Guava\Calendar\Widgets\CalendarWidget as BaseCalendarWidget;

class CalendarWidget extends BaseCalendarWidget
{
    protected string $calendarView = 'resourceTimeGridDay';

    protected string | Closure | HtmlString | null $heading = 'Reservation Calendar';
    protected bool $eventClickEnabled = true;

    protected array|Closure $options = [
        'slotMinTime' => '08:00:00',
        'slotMaxTime' => '20:00:00',
        'slotDuration' => '00:15:00',
        'slotEventOverlap' => false,
        'allDaySlot' => false,
        'datesAboveResources' => true,
        'buttonText' => [
            'resourceTimeGridWeek' => 'Weeks',
            'resourceTimeGridDay' => 'Days',
            'today' => 'Today'
        ],
        'headerToolbar' => [
            'start' => 'resourceTimeGridDay, resourceTimeGridWeek',
            'center' => 'title',
            'end' => 'prev, today, next',
        ]
    ];
    public function getEvents(array $fetchInfo = []): Collection|array
    {
        return Reservation::query()
            ->with('operation')
            ->where('start_time', '>=', $fetchInfo['start'])
            ->where('end_time', '<=', $fetchInfo['end'])
            ->get()
            ->flatMap(function (Reservation $reservation) {

                $events = [];

                $events[] = Event::make($reservation)
                    ->resourceId($reservation->machine->id)
                    ->title($reservation->operation->name)
                    ->start($reservation->start_time)
                    ->end($reservation->end_time)
                    ->backgroundColor($reservation->operation->color)
                    ->textColor('#314155')
                    ->extendedProps([
                            'client' => $reservation->client->name,
                            'user' => $reservation->user->name
                        ]);

                if ($reservation->break_time) {
                    $events[] = Event::make($reservation)
                        ->resourceId($reservation->machine->id)
                        ->title('Break Time')
                        ->start($reservation->end_time)
                        ->end($reservation->break_time)
                        ->backgroundColor('#FF7F7F')
                        ->textColor('#000000');
                }

                return $events;
            })->toArray();
    }

    public function getEventContent(): null|string|array
    {
        return view('components.calendar.events.event');
    }

    public function getResources(): Collection|array
    {
        return Machine::query()
            ->get()
            ->map(function (Machine $machine) {
                return Resource::make($machine->id)
                    ->title($machine->name)
                    ->toArray();
            })
            ->toArray();
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            self::viewAction(),
            self::editAction(),
            $this->deleteAction()
        ];
    }
    public function getHeaderActions(): array
    {
        return [
          CreateAction::make('CreateReservation')
                ->model(Reservation::class)
                ->action(function (array $data) {

                    $data = ReservationService::createAction($data);

                    Reservation::query()->create($data);

                    Notification::make()
                        ->title('Reservation Created')
                        ->success()
                        ->send();
                })
        ];
    }

    public function editAction(): Action
    {
        return EditAction::make('EditReservation')
                ->model(Reservation::class)
                ->form(ReservationService::editForm())
                ->action(function (array $data) {
                    $data = ReservationService::editAction($data);

                    $reservation = $this->getEventRecord();

                    $reservation->updateOrFail($data);

                    Notification::make()
                        ->title('Reservation Created')
                        ->success()
                        ->send();
                });
    }

    public function viewAction(): Action
    {
        return ViewAction::make('ViewReservation')
            ->model(Reservation::class)
            ->form(ReservationService::viewForm());
    }

    public function getSchema(?string $model = null): ?array
    {
        return ReservationService::createForm();
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
