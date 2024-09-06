<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Support\Collection;

class CalendarWidget extends \Guava\Calendar\Widgets\CalendarWidget
{
    protected string $calendarView = 'resourceTimeGridDay';
    protected bool $eventClickEnabled = true;
    protected ?string $defaultEventClickAction = 'edit';

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $reservationId = $info['event']['key'] ?? null;

        if ($reservationId) {
            $reservation = Reservation::query()->find($reservationId);
            if ($reservation) {
                $this->editAction();
            } else {
                Notification::make()
                    ->title('Reservation not found')
                    ->danger()
                    ->send();
            }
        }
    }

    public function editAction(): Action
    {
        return Action::make('Edit Reservation')
            ->modalHeading('Edit Reservation')
            ->modalWidth('lg')
            ->action(function (array $data) {
                $reservation = Reservation::find($data['id']);
                if ($reservation) {
                    $reservation->update($data);
                    Notification::make()
                        ->title('Reservation updated successfully')
                        ->success()
                        ->send();
                }
            });
    }

    public function getSchema(?string $model = null): ?array
    {
        return [
            // Client Field (Disabled)
            Select::make('client_id')
                ->label('Client')
                ->options(Client::all()->pluck('name', 'id'))
                ->disabled(),

            // User Field (Disabled)
            Select::make('user_id')
                ->label('Created By User')
                ->options(User::all()->pluck('name', 'id'))
                ->disabled(),

            // Machine Field (Disabled)
            Select::make('machine_id')
                ->label('Machine')
                ->options(Machine::all()->pluck('name', 'id'))
                ->disabled(),

            // Operation Field (Required)
            Select::make('operation_id')
                ->label('Operation')
                ->options(Operation::all()->pluck('name', 'id'))
                ->required(),

            // Time Fields (Disabled)
            TextInput::make('start_time')->disabled(),
            TextInput::make('end_time')->disabled(),
            TextInput::make('break_time')->disabled(),

            // Section for Changing Reservation Time
            Section::make('Change the reservation time')
                ->schema([
                    // Date Picker (Required)
                    DatePicker::make('date')
                        ->minDate(now()->format('Y-m-d'))
                        ->maxDate(now()->addWeeks(2)->format('Y-m-d'))
                        ->required()
                        ->live(),

                    // Start Time Select (Hidden until Date is Selected)
                    Select::make('start')
                        ->options(fn(Get $get) => (new ReservationService())
                            ->getAvailableTimesForDate($get('date'), $get('id')))
                        ->hidden(fn(Get $get) => !$get('date'))
                        ->required()
                        ->searchable()
                        ->live(),

                    // Duration Select (Hidden until Start Time is Selected)
                    Select::make('duration')
                        ->label('Duration')
                        ->options(fn(Get $get) => (new ReservationService())
                            ->getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                        ->helperText(fn(Get $get) => (new ReservationService())
                            ->getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                        ->hidden(fn(Get $get) => !$get('start'))
                        ->required()
                        ->live(),

                    // Break Time Select (Hidden until Duration is Selected)
                    Select::make('break')
                        ->label('Break Time')
                        ->options(fn(Get $get) => (new ReservationService())
                            ->getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                        ->helperText('You can add a break time after the reservation')
                        ->hidden(fn(Get $get) => !$get('duration'))
                        ->disabled(fn(Get $get) => (new ReservationService())
                            ->disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                ]),
        ];
    }

    protected array|Closure $options = [
        'slotMinTime' => '08:00:00',
        'slotMaxTime' => '20:00:00',
        'slotDuration' => '00:15:00',
        'slotEventOverlap' => false,
        'allDaySlot' => false,
//        'theme' => [
//            'allDay' => 'ec-all-day',
//            'active' => 'ec-active',
//            'bgEvent' => 'ec-bg-event',
//            'bgEvents' => 'ec-bg-events',
//            'body' => 'ec-body',
//            'button' => 'ec-button',
//            'buttonGroup' => 'ec-button-group',
//            'calendar' => 'ec',
//            'compact' => 'ec-compact',
//            'container' => 'ec-container',
//            'content' => 'ec-content',
//            'day' => 'ec-day',
//            'dayHead' => 'ec-day-head',
//            'dayFoot' => 'ec-day-foot',
//            'days' => 'ec-days',
//            'daySide' => 'ec-day-side',
//            'draggable' => 'ec-draggable',
//            'dragging' => 'ec-dragging',
//            'event' => 'ec-event',
//            'eventBody' => 'ec-event-body',
//            'eventTag' => 'ec-event-tag',
//            'eventTime' => 'ec-event-time',
//            'eventTitle' => 'ec-event-title text-xs', // Add custom class here while keeping default
//            'events' => 'ec-events',
//            'extra' => 'ec-extra',
//            'ghost' => 'ec-ghost',
//            'handle' => 'ec-handle',
//            'header' => 'ec-header',
//            'hiddenScroll' => 'ec-hidden-scroll',
//            'highlight' => 'ec-highlight',
//            'icon' => 'ec-icon',
//            'line' => 'ec-line',
//            'lines' => 'ec-lines',
//            'main' => 'ec-main',
//            'noEvents' => 'ec-no-events',
//            'nowIndicator' => 'ec-now-indicator',
//            'otherMonth' => 'ec-other-month',
//            'pointer' => 'ec-pointer',
//            'popup' => 'ec-popup',
//            'preview' => 'ec-preview',
//            'resizer' => 'ec-resizer',
//            'resizingX' => 'ec-resizing-x',
//            'resizingY' => 'ec-resizing-y',
//            'resource' => 'ec-resource',
//            'selecting' => 'ec-selecting',
//            'sidebar' => 'ec-sidebar',
//            'sidebarTitle' => 'ec-sidebar-title',
//            'today' => 'ec-today',
//            'time' => 'ec-time',
//            'times' => 'ec-times',
//            'title' => 'ec-title',
//            'toolbar' => 'ec-toolbar',
//            'view' => 'ec-timeline ec-resource-week-view',
//            'weekdays' => [
//                'ec-sun', 'ec-mon', 'ec-tue', 'ec-wed', 'ec-thu', 'ec-fri', 'ec-sat'
//            ],
//            'withScroll' => 'ec-with-scroll',
//            'uniform' => 'ec-uniform',
//        ],
//        'view' => 'ec-time-grid ec-week-view'
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

                $events[] = Event::make()
                    ->key($reservation->id)
                    ->resourceId($reservation->machine->id)
                    ->title($reservation->operation->name)
                    ->start($reservation->start_time)
                    ->end($reservation->end_time)
                    ->backgroundColor($reservation->operation->color)
                    ->action('edit');

                if ($reservation->break_time) {
                    $events[] = Event::make()
                        ->key($reservation->id)
                        ->resourceId($reservation->machine->id)
                        ->title('Break Time')
                        ->start($reservation->end_time)
                        ->end($reservation->break_time)
                        ->backgroundColor('#f54e4e');
                }

                return $events;
            })->toArray();
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
}
