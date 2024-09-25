<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\VerticalAlignment;
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
    protected bool $dateClickEnabled = true;

    public function onDateClick(array $info = []): void
    {
        $this->setOption('date', $info['dateStr']);
        $this->setOption('view', 'resourceTimeGridDay');
    }

    protected array|Closure $options = [
        'slotMinTime' => '08:00:00',
        'slotMaxTime' => '20:00:00',
        'slotDuration' => '00:15:00',
        'slotEventOverlap' => false,
        'allDaySlot' => false,
        'datesAboveResources' => true,
        'buttonText' => [
            'dayGridMonth' => 'Month',
            'resourceTimeGridWeek' => 'Week',
            'resourceTimeGridDay' => 'Day',
            'today' => 'Today'
        ],
        'headerToolbar' => [
            'start' => 'resourceTimeGridDay, resourceTimeGridWeek, dayGridMonth',
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

                if ($reservation->status !== ReservationStatus::CANCELED) {
                    $events[] = Event::make($reservation)
                        ->resourceId($reservation->machine->id)
                        ->title($operationNames)
                        ->start($reservation->start_time)
                        ->end($reservation->end_time)
                        ->backgroundColor(match ($reservation->getReservationStatusAttribute()) {
                            ReservationStatus::FINISHED => '#bbcafc',
                            ReservationStatus::PENDING_FINISH => '#e1e2e3',
                            default => $reservation->operations->first()->color
                        })
                        ->textColor('#314155')
                        ->extendedProps([
                            'client' => $reservation->client->name,
                            'assigned_user' => $reservation->assigned_user->name,
                            'total_price' => $reservation->getTotalPriceAttribute(),
                            'status' => $reservation->getReservationStatusAttribute(),
                            'icon' => match ($reservation->getReservationStatusAttribute()) {
                                ReservationStatus::ONGOING => svg('heroicon-o-minus-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(79%) sepia(66%) saturate(2254%) hue-rotate(352deg) brightness(103%) contrast(104%);'])->toHtml(),
                                ReservationStatus::SCHEDULED => svg('heroicon-o-clock', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(53%) sepia(20%) saturate(1649%) hue-rotate(81deg) brightness(94%) contrast(88%);'])->toHtml(),
                                ReservationStatus::FINISHED => svg('heroicon-o-check-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(31%) sepia(28%) saturate(6136%) hue-rotate(200deg) brightness(104%) contrast(105%);'])->toHtml(),
                                ReservationStatus::CANCELED => svg('heroicon-o-x-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(38%) sepia(68%) saturate(5599%) hue-rotate(337deg) brightness(90%) contrast(90%);'])->toHtml(),
                                ReservationStatus::PENDING_FINISH => svg('heroicon-o-exclamation-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(46%) sepia(17%) saturate(241%) hue-rotate(167deg) brightness(94%) contrast(86%);'])->toHtml(),
                            }
                        ]);
                }


                if ($reservation->break_time && $reservation->status !== ReservationStatus::CANCELED) {
                    $events[] = Event::make($reservation)
                        ->resourceId($reservation->machine->id)
                        ->title('Break Time')
                        ->start($reservation->end_time)
                        ->end($reservation->break_time)
                        ->backgroundColor($reservation->status === ReservationStatus::FINISHED ? '#f7d0e0' : '#FF7F7F')
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

//    public function onEventClick(array $info = [], ?string $action = null): void
//    {
//        $status = data_get($info, 'event.extendedProps.status');
//
//
//        if ($status === ReservationStatus::FINISHED) {
//            $this->defaultEventClickAction = 'ViewReservation';
//        }
//
//    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            self::viewAction(),
            self::editAction(),
            $this->deleteAction(),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('createMultipleReservations')
                ->label('Create Reservations')
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
                                Select::make('assigned_user_id')
                                    ->label('Assigned User')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->nullable()
                                    ->searchable(),
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
                            'assigned_user_id' => $reservation['assigned_user_id'],
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
                    ->title('Reservation Edited')
                    ->success()
                    ->send();
            });
    }

    public function viewAction(): Action
    {
        return ViewAction::make('ViewReservation')
            ->model(Reservation::class)
            ->form([
                Section::make()->schema([
                    Placeholder::make('status')
                        ->label(new HtmlString('<span class="text-lg font-extralight text-center">Status</span>'))
                        ->content(function (Reservation $record): HtmlString {
                        $svg = match ($record->getReservationStatusAttribute()) {
                            ReservationStatus::ONGOING => svg('heroicon-o-minus-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(79%) sepia(66%) saturate(2254%) hue-rotate(352deg) brightness(103%) contrast(104%);'])->toHtml(),
                            ReservationStatus::SCHEDULED => svg('heroicon-o-clock', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(53%) sepia(20%) saturate(1649%) hue-rotate(81deg) brightness(94%) contrast(88%);'])->toHtml(),
                            ReservationStatus::FINISHED => svg('heroicon-o-check-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(31%) sepia(28%) saturate(6136%) hue-rotate(200deg) brightness(104%) contrast(105%);'])->toHtml(),
                            ReservationStatus::CANCELED => svg('heroicon-o-x-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(38%) sepia(68%) saturate(5599%) hue-rotate(337deg) brightness(90%) contrast(90%);'])->toHtml(),
                            ReservationStatus::PENDING_FINISH => svg('heroicon-o-exclamation-circle', 'w-6 h-6', ['style' => 'filter: brightness(0) saturate(100%) invert(46%) sepia(17%) saturate(241%) hue-rotate(167deg) brightness(94%) contrast(86%);'])->toHtml(),
                        };

                        return new HtmlString("
                            <div class=\"flex items-center\">
                                <span class=\"px-1 text-lg\">{$record->getReservationStatusAttribute()->value}</span>
                                {$svg}
                            </div>
                        ");
                    }),
                    Placeholder::make('total_price')
                        ->label(new HtmlString('<span class="text-lg font-extralight text-center">Total Price</span>'))
                        ->content(function (Reservation $record): HtmlString {
                            return new HtmlString("
                            <span class=\"px-1 text-lg font-semibold text-center\">{$record->getTotalPriceAttribute()} MKD</span>
                        ");
                        }),
                    Actions::make([
                        \Filament\Forms\Components\Actions\Action::make('Cancel Reservation')
                            ->color('danger')
                            ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::CANCELED ||
                                $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                                $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                            ->action(function (Reservation $record) {
                                try {
                                    $record->update(['status' => ReservationStatus::CANCELED]);
                                    $this->refreshRecords();
                                    time_nanosleep(1, 0);

                                    $this->closeActionModal();

                                    Notification::make()
                                        ->title('Canceled Reservation')
                                        ->success()
                                        ->send();

                                } catch (\Exception $e) {
                                    $this->closeActionModal();

                                    Notification::make()
                                        ->title('Failed to cancel reservation.')
                                        ->danger()
                                        ->send();

                                }
                            }),
                        \Filament\Forms\Components\Actions\Action::make('Finish Reservation')
                            ->color('primary')
                            ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH)
                            ->hidden(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED || $record->getReservationStatusAttribute() === ReservationStatus::CANCELED)
                            ->action(function ($record) {
                                try {
                                    $record->update(['status' => ReservationStatus::FINISHED]);
                                    time_nanosleep(1, 0);
                                    $this->refreshRecords();

                                    $this->closeActionModal();

                                    Notification::make()
                                        ->title('Finished Reservation')
                                        ->success()
                                        ->send();
                                } catch (\Exception $e) {

                                    $this->closeActionModal();

                                    Notification::make()
                                        ->title('Failed to finish reservation.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        \Filament\Forms\Components\Actions\Action::make('Invoice')
                            ->icon('heroicon-o-document-arrow-down')
                            ->label('Generate Invoice')
                            ->color('primary')
                            ->visible(fn(Reservation $record) => $record->getReservationStatusAttribute() === ReservationStatus::FINISHED)
                            ->url(fn (Reservation $record) => route('reservation.invoice.download', $record))->openUrlInNewTab(),
                    ])->verticallyAlignCenter(),
                    Select::make('client_id')
                        ->label('Client Name')
                        ->options(Client::all()->pluck('name', 'id'))
                        ->disabled(),
                    Select::make('client_id')
                        ->label('Client Telephone')
                        ->options(Client::all()->pluck('telephone', 'id')->map(fn($telephone) => $telephone ?? 'N/A'))
                        ->disabled(),
                    Select::make('client_id')
                        ->label('Client Email')
                        ->options(Client::all()->pluck('email', 'id')->map(fn($email) => $email ?? 'N/A'))
                        ->disabled(),
                    Select::make('user_id')->label('Created By User')->options(User::all()->pluck('name', 'id')->lazy())->disabled()->columnSpan(2),
                    Select::make('assigned_user_id')->label('Assigned User')->options(User::all()->pluck('name', 'id')->lazy())->disabled(),
                    Select::make('machine_id')->label('Machine')->options(Machine::all()->pluck('name', 'id')->lazy())->disabled()->columnSpan(2),
                    Select::make('operations')->relationship('operations', 'name')->label('Operation')->options(Operation::all()->pluck('name', 'id')->lazy())->multiple()->disabled(),
                    DateTimePicker::make('start_time')->label('Date and Start Time')->format('D, d M Y H:i')->disabled(),
                    TimePicker::make('end_time')->time('H:i')->disabled(),
                    TimePicker::make('break_time')->time('H:i')->disabled()
                ])->columns(3)->columnSpan(2),
            ]);
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
        if (($arguments[0]['status'] === ReservationStatus::FINISHED || $arguments[0]['end_time'] <= now()->timezone('GMT+2'))  && ($ability === 'delete' || $ability === 'EditReservation')) {
            $this->cachedContextMenuActions['eventClick'][2]->hidden(true);
            $this->cachedContextMenuActions['eventClick'][2]->disabled(true);
            $this->cachedContextMenuActions['eventClick'][2]->visible(false);
            $this->cachedContextMenuActions['eventClick'][1]->hidden(true);
            $this->cachedContextMenuActions['eventClick'][1]->disabled(true);
            $this->cachedContextMenuActions['eventClick'][1]->visible(false);
        }
        return true;
    }
}
