<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Services\ReservationService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
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

    protected string|Closure|HtmlString|null $heading = 'Reservation Calendar';
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
            ->with('operations')
            ->where('start_time', '>=', $fetchInfo['start'])
            ->where('end_time', '<=', $fetchInfo['end'])
            ->get()
            ->flatMap(function (Reservation $reservation) {

                $events = [];

                $operationNames = $reservation->operations->pluck('name')->implode(', ');

                $events[] = Event::make($reservation)
                    ->resourceId($reservation->machine->id)
                    ->title($operationNames)
                    ->start($reservation->start_time)
                    ->end($reservation->end_time)
                    ->backgroundColor($reservation->operations->first()->color)
                    ->textColor('#314155')
                    ->extendedProps([
                        'client' => $reservation->client->name,
                        'user' => $reservation->user->name,
                        'total_price' => $reservation->getTotalPriceAttribute()
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
            CreateAction::make('createReservation')
                ->label('New Reservation')
                ->model(Reservation::class)
                ->action(function (array $data) {

                    $data = ReservationService::createAction($data);

                    $reservation = Reservation::query()->create($data);

                    $reservation->operations()->sync($data['operations']);

                    Notification::make()
                        ->title('Reservation Created')
                        ->success()
                        ->send();

                    $this->refreshRecords();
                }),
            CreateAction::make('createMultipleReservations')
                ->label('Create Multiple Reservations')
                ->model(Reservation::class)
                ->form([
                    Select::make('client_id')
                        ->label('Client')
                        ->options(Client::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->createOptionForm([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('email')->label('Email')->required()->email()->unique(Client::class, 'email'),
                            TextInput::make('telephone')->label('Telephone')->unique(Client::class, 'telephone')->nullable(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $client = Client::query()->create($data);
                            return $client->id;
                        }),

                    Repeater::make('reservations')
                        ->label('')
                        ->schema(function () {

                            return [
                                Select::make('machine_id')
                                    ->label('Machine')
                                    ->options(Machine::all()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->live(),
                                Select::make('operations')
                                    ->label('Operations')
                                    ->multiple()
                                    ->searchable()
                                    ->required()
                                    ->hidden(fn(Get $get) => !$get('machine_id'))
                                    ->options(
                                        fn(Get $get) => Operation::query()->whereHas('machines', function ($query) use ($get) {
                                            $query->where('machine_id', $get('machine_id'));
                                        })->pluck('name', 'id')
                                    )
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('description'),
                                        TextInput::make('price')->numeric()->required(),
                                        ColorPicker::make('color')->required(),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $operation = Operation::query()->create([
                                            'name' => $data['name'],
                                            'description' => $data['description'],
                                            'color' => $data['color'],
                                            'price' => $data['price'],
                                        ]);

                                        return $operation->id;
                                    })
                                    ->live()
                                    ->reactive(),
                                DatePicker::make('date')
                                    ->minDate(now()->format('Y-m-d'))
                                    ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                                    ->hidden(fn(Get $get) => !$get('operations'))
                                    ->required()
                                    ->live(),
                                Select::make('start_time')
                                    ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date')))
                                    ->hidden(fn(Get $get) => !$get('date'))
                                    ->required()
                                    ->searchable()
                                    ->live(),
                                Select::make('duration')
                                    ->label('Duration')
                                    ->options(fn(Get $get) => ReservationService::getDurations($get('machine_id'), $get('date') ?? '', $get('start_time') ?? ''))
                                    ->helperText(fn(Get $get) => ReservationService::getNextReservationStartTime($get('machine_id'), $get('date') ?? '', $get('start_time') ?? ''))
                                    ->hidden(fn(Get $get) => !$get('start_time'))
                                    ->required()
                                    ->live(),
                                Select::make('break')
                                    ->label('Break Time')
                                    ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id'), $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                                    ->helperText('You can add a break time after the reservation')
                                    ->hidden(fn(Get $get) => !$get('duration'))
                                    ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id'), $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                            ];
                        })
                        ->addActionLabel('Add Another Reservation')
                        ->live()
                        ->reactive()
                        ->hidden(fn(Get $get) => !$get('client_id'))
                        ->defaultItems(1)
                        ->itemLabel(function () {
                            static $position = 1;
                            return 'Reservation #' . $position++;
                        })
                        ->addable()
                        ->deletable()
                        ->collapsible()
                        ->reorderable(false)
                ])
                ->action(function (array $data) {
                    foreach ($data['reservations'] as $reservation) {
                        $attributes = ReservationService::createAction([
                            'client_id' => $data['client_id'],
                            'machine_id' => $reservation['machine_id'],
                            'date' => $reservation['date'],
                            'start_time' => $reservation['start_time'],
                            'duration' => $reservation['duration'],
                            'break' => $reservation['break'],
                        ]);

                        $res = Reservation::query()->create($attributes);

                        $res->operations()->sync($reservation['operations']);
                    }

                    Notification::make()
                        ->title('Reservations Created')
                        ->success()
                        ->send();

                    $this->refreshRecords();
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

                $this->refreshRecords();

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
        return [Select::make('client_id')
            ->label('Client')
            ->options(Client::all()->pluck('name', 'id'))
            ->searchable()
            ->required()
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->unique(Client::class, 'email'),
                TextInput::make('telephone')
                    ->label('Telephone')
                    ->unique(Client::class, 'telephone')
                    ->nullable(),
            ])
            ->createOptionUsing(function (array $data): int {
                $client = Client::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'telephone' => $data['telephone'],
                ]);

                return $client->id;
            }),
            Select::make('machine_id')
                ->label('Machine')
                ->options(Machine::all()->pluck('name', 'id'))
                ->required()
                ->live()
                ->reactive()
                ->afterStateUpdated(function (callable $set) {
                    $set('date', null);
                    $set('start_time', null);
                    $set('duration', null);
                    $set('break', null);
                }),
            Select::make('operations')
                ->label('Operations')
                ->multiple()
                ->searchable()
                ->required()
                ->hidden(fn(Get $get) => !$get('machine_id'))
                ->options(
                    fn(Get $get) => Operation::query()->whereHas('machines', function ($query) use ($get) {
                        $query->where('machine_id', $get('machine_id'));
                    })->pluck('name', 'id')
                )
                ->createOptionForm([
                    TextInput::make('name')->required(),
                    TextInput::make('description'),
                    TextInput::make('price')->numeric()->required(),
                    ColorPicker::make('color')->required()
                ])
                ->createOptionUsing(function (array $data): int {
                    $operation = Operation::query()->create([
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'color' => $data['color'],
                        'price' => $data['price'],
                    ]);

                    return $operation->id;
                })->live(),
            DatePicker::make('date')
                ->minDate(now()->format('Y-m-d'))
                ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                ->hidden(fn(Get $get) => !$get('operations'))
                ->required()
                ->live(),
            Select::make('start_time')
                ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date')))
                ->hidden(fn(Get $get) => !$get('date'))
                ->required()
                ->searchable()
                ->live(),
            Select::make('duration')
                ->label('Duration')
                ->options(fn(Get $get) => ReservationService::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                ->helperText(fn(Get $get) => ReservationService::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                ->hidden(fn(Get $get) => !$get('start_time'))
                ->required()
                ->live(),
            Select::make('break')
                ->label('Break Time')
                ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                ->helperText('You can add a break time after the reservation')
                ->hidden(fn(Get $get) => !$get('duration'))
                ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
        ];
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
