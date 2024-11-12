<?php

namespace App\Filament\App\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\App\Resources\ReservationResource\Components\Forms\EditReservationForm;
use App\Filament\App\Resources\ReservationResource\Components\Forms\ViewReservationForm;
use App\Filament\App\Resources\ReservationResource\Components\Inputs\ReservationInputs;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Settings\CalendarSettings;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Guava\Calendar\Actions\CreateAction;
use Guava\Calendar\Actions\EditAction;
use Guava\Calendar\Actions\ViewAction;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\Widgets\CalendarWidget as BaseCalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class CalendarWidget extends BaseCalendarWidget
{
    protected string $calendarView = 'timeGridDay';

    public ?string $selectedMachine = null;

    public function getHeading(): string|HtmlString
    {
        return __('Reservation Calendar');
    }

    protected bool $eventClickEnabled = true;
    protected bool $dateClickEnabled = true;

    public function onDateClick(array $info = []): void
    {
        $this->setOption('date', $info['dateStr']);
        $this->setOption('view', 'timeGridDay');
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => app(CalendarSettings::class)->slotMinTime,
            'slotMaxTime' => app(CalendarSettings::class)->slotMaxTime,
            'slotDuration' => app(CalendarSettings::class)->slotDuration,
            'slotLabelFormat' => [
                "hour" => 'numeric',
                "minute" => '2-digit',
                "hour12" => false,
            ],
            'slotEventOverlap' => false,
            'allDaySlot' => false,
            'buttonText' => [
                'dayGridMonth' => __('Month'),
                'timeGridWeek' => __('Week'),
                'timeGridDay' => __('Day'),
                'today' => __('Today'),
            ],
            'headerToolbar' => [
                'start' => 'timeGridDay, timeGridWeek, dayGridMonth',
                'center' => 'title',
                'end' => 'prev, today, next',
            ],
        ];
    }

    public function getEvents(array $fetchInfo = []): Collection|array
    {
        return Reservation::query()
            ->with('operations')
            ->where('start_time', '>=', $fetchInfo['start'])
            ->where('end_time', '<=', $fetchInfo['end'])
            ->where('machine_id', $this->selectedMachine)
            ->get()
            ->flatMap(function (Reservation $reservation) {

                $events = [];

                $operationNames = $reservation->operations->pluck('name')->implode(', ');

                if ($reservation->status !== ReservationStatus::CANCELED) {
                    $events[] = Event::make($reservation)
                        ->title($operationNames)
                        ->start(Carbon::parse($reservation->start_time)->addHour())
                        ->end(Carbon::parse($reservation->end_time)->addHour())
                        ->backgroundColor(match ($reservation->getReservationStatusAttribute()) {
                            ReservationStatus::FINISHED => '#bbcafc',
                            ReservationStatus::PENDING_FINISH => '#e1e2e3',
                            default => $reservation->operations->first()->color
                        })
                        ->textColor('#314155')
                        ->extendedProps([
                            'time' => Carbon::parse($reservation->start_time)->translatedFormat('H:i')
                                . ' - ' .
                                Carbon::parse($reservation->end_time)->translatedFormat('H:i'),
                            'client' => $reservation->client->name ?? __('N/A'),
                            'assigned_user' => $reservation->assigned_user->name ?? __('N/A'),
                            'total_price' => $reservation->getTotalPriceAttribute(),
                            'status' => __($reservation->getReservationStatusAttribute()->value),
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
                        ->title('Break Time')
                        ->start(Carbon::parse($reservation->end_time)->addHour())
                        ->end(Carbon::parse($reservation->break_time)->addHour())
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
            CreateAction::make('CreateReservation')
                ->label(__('Create Reservation'))
                ->model(Reservation::class)
                ->modelLabel(__('Reservation'))
                ->createAnother(false)
                ->form([
                    Grid::make(3)
                        ->schema([
                            ReservationInputs::selectClient(),
                            ReservationInputs::selectMachine(),
                            ReservationInputs::selectAssignedUser(),
                            ReservationInputs::selectMultipleOperations(),
                            ReservationInputs::selectDate(),
                            ReservationInputs::selectStartTime(),
                            ReservationInputs::selectDuration(),
                            ReservationInputs::selectBreakDuration()
                        ])->columnSpan(2),
                ])
                ->action(function (array $data) {
                    $data = ReservationService::createAction($data);

                    $reservation = Reservation::query()->create($data);

                    $reservation->operations()->attach($data['operations']);

                    Notification::make()
                        ->title(__('Reservation Created'))
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
            ->form(EditReservationForm::form())
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
                Section::make()->schema(
                    ViewReservationForm::form(fn() => $this->refreshRecords())
                )->columns(3)->columnSpan(2),
            ]);
    }

    public function authorize($ability, $arguments = []): bool
    {
        if (($arguments[0]['status'] === ReservationStatus::FINISHED || $arguments[0]['end_time'] <= now()) && ($ability === 'delete' || $ability === 'EditReservation')) {
            $this->cachedContextMenuActions['eventClick'][2]->hidden(true);
            $this->cachedContextMenuActions['eventClick'][2]->disabled(true);
            $this->cachedContextMenuActions['eventClick'][2]->visible(false);
            if ($arguments[0]['status'] === ReservationStatus::FINISHED) {
                $this->cachedContextMenuActions['eventClick'][1]->hidden(true);
                $this->cachedContextMenuActions['eventClick'][1]->disabled(true);
                $this->cachedContextMenuActions['eventClick'][1]->visible(false);
            }
        }
        return true;
    }
}
