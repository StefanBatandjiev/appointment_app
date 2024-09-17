<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
class ReservationService
{
    public static function createAction(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (isset($data['start_time']) && isset($data['duration'])) {
            $date = Carbon::parse($data['date']);

            [$hour, $minute] = explode(':', $data['start_time']);

            $startTime = $date->setTime((int)$hour, (int)$minute);
            $data['start_time'] = $startTime;

            $duration = (int)$data['duration'];

            $data['end_time'] = (clone $startTime)->addMinutes($duration);

            if (isset($data['break'])) {
                $data['break_time'] = (clone $data['end_time'])->addMinutes((int)$data['break']);
            }
        }

        return $data;
    }

    public static function createForm(): array
    {
        return [
            Select::make('client_id')
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
            Select::make('operation_id')
                ->label('Operation')
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
                    ColorPicker::make('color')->required()
                ])
                ->createOptionUsing(function (array $data): int {
                    $operation = Operation::query()->create([
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'color' => $data['color'],
                    ]);

                    return $operation->id;
                })->live(),
            DatePicker::make('date')
                ->minDate(now()->format('Y-m-d'))
                ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                ->hidden(fn(Get $get) => !$get('operation_id'))
                ->required()
                ->live(),
            Select::make('start_time')
                ->options(fn(Get $get) => self::getAvailableTimesForDate($get('machine_id'), $get('date')))
                ->hidden(fn(Get $get) => !$get('date'))
                ->required()
                ->searchable()
                ->live(),
            Select::make('duration')
                ->label('Duration')
                ->options(fn(Get $get) => self::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                ->helperText(fn(Get $get) => self::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                ->hidden(fn(Get $get) => !$get('start_time'))
                ->required()
                ->live(),
            Select::make('break')
                ->label('Break Time')
                ->options(fn(Get $get) => self::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                ->helperText('You can add a break time after the reservation')
                ->hidden(fn(Get $get) => !$get('duration'))
                ->disabled(fn(Get $get) => self::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))];
    }

    public static function editAction(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (isset($data['start']) && isset($data['duration'])) {
            $date = Carbon::parse($data['date']);

            [$hour, $minute] = explode(':', $data['start']);

            $startTime = $date->setTime((int)$hour, (int)$minute);
            $data['start_time'] = $startTime;

            $duration = (int) $data['duration'];

            $data['end_time'] = (clone $startTime)->addMinutes($duration);

            if (isset($data['break'])) {
                $data['break_time'] = (clone $data['end_time'])->addMinutes((int)$data['break']);
            }
        }

        unset($data['date']);
        unset($data['start']);
        unset($data['break']);
        unset($data['duration']);

        return $data;
    }

    public static function editForm(): array
    {
        return [
            Select::make('client_id')
                ->label('Client')
                ->options(Client::all()->pluck('name', 'id'))
                ->disabled(),
            Select::make('user_id')
                ->label('Created By User')
                ->options(User::all()->pluck('name', 'id'))
                ->disabled(),
            Select::make('machine_id')
                ->label('Machine')
                ->options(Machine::all()->pluck('name', 'id'))
                ->disabled(),
            Select::make('operation_id')
                ->label('Operation')
                ->options(
                    fn(Get $get) => Operation::query()->whereHas('machines', function ($query) use ($get) {
                        $query->where('machine_id', $get('machine_id'));
                    })->pluck('name', 'id')
                )
                ->searchable()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->required(),
                    TextInput::make('description'),
                    ColorPicker::make('color')->required()
                ])
                ->createOptionUsing(function (array $data): int {
                    $operation = Operation::query()->create([
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'color' => $data['color'],
                    ]);

                    return $operation->id;
                }),
            TextInput::make('start_time')->disabled(),
            TextInput::make('end_time')->disabled(),
            TextInput::make('break_time')->disabled(),
            Section::make('Change the reservation time')->schema([
                DatePicker::make('date')
                    ->minDate(now()->format('Y-m-d'))
                    ->maxDate(now()->addMonths(2)->format('Y-m-d'))
                    ->live(),
                Select::make('start')
                    ->options(fn(Get $get) => self::getAvailableTimesForDate($get('machine_id'), $get('date'), $get('id')))
                    ->hidden(fn(Get $get) => !$get('date'))
                    ->required()
                    ->searchable()
                    ->live(),
                Select::make('duration')
                    ->label('Duration')
                    ->options(fn(Get $get) => self::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                    ->helperText(fn(Get $get) => self::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                    ->hidden(fn(Get $get) => !$get('start'))
                    ->required()
                    ->live(),
                Select::make('break')
                    ->label('Break Time')
                    ->options(fn(Get $get) => self::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                    ->helperText('You can add a break time after the reservation')
                    ->hidden(fn(Get $get) => !$get('duration'))
                    ->disabled(fn(Get $get) => self::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
            ])];
    }

    public static function viewForm(): array
    {
        return [
            Section::make()
                ->schema([
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
                    Select::make('user_id')
                        ->label('Created By User')
                        ->options(User::all()->pluck('name', 'id'))
                        ->disabled(),
                    Select::make('machine_id')
                        ->label('Machine')
                        ->options(Machine::all()->pluck('name', 'id'))
                        ->disabled(),
                    Select::make('operation_id')
                        ->label('Operation')
                        ->options(Operation::all()->pluck('name', 'id'))
                        ->disabled(),
                    DateTimePicker::make('start_time')->label('Date and Start Time')->format('D, d M Y H:i')->disabled(),
                    TimePicker::make('end_time')->time('H:i')->disabled(),
                    TimePicker::make('break_time')->time('H:i')->disabled(),
                ])
                ->columns()
        ];
    }

    public static function getAvailableTimesForDate(int $machine_id, string $date, int $reservationId = null): array
    {
        $date = Carbon::parse($date);
        $currentDate = now()->timezone('GMT+2');
        $startPeriod = $date->copy()->setTime(8, 0);
        $endPeriod = $date->copy()->setTime(20, 0);

        if ($date->isToday() && $currentDate->hour > 8) {
            $roundedMinutes = ceil($currentDate->minute / 5) * 5;

            if ($roundedMinutes == 60) {
                $roundedMinutes = 0;
                $currentDate->addHour();
            }

            $startPeriod = $date->copy()->setTime($currentDate->hour, $roundedMinutes);
        }

        $times = CarbonPeriod::create($startPeriod, '5 minutes', $endPeriod);
        $availableReservations = [];

        $reservations = Reservation::query()->where('machine_id', '=', $machine_id)->whereDate('start_time', $date);

        if ($reservationId) {
            $currentReservation = Reservation::query()->find($reservationId);

            $reservations = $reservations->where('id', '!=', $currentReservation->id);
        }

        $reservations = $reservations->get(['start_time', 'end_time', 'break_time']);

        foreach ($times as $time) {
            $isAvailable = true;

            if ($reservationId && isset($currentReservation)) {
                $currentStartTime = Carbon::parse($currentReservation->start_time);
                $currentEndTime = Carbon::parse($currentReservation->end_time);

                if ($time->between($currentStartTime, $currentEndTime->subMinute())) {
                    $isAvailable = true;
                }
            }

            foreach ($reservations as $reservation) {
                $startTime = Carbon::parse($reservation->start_time);
                $endTime = Carbon::parse($reservation->end_time);
                $break_time = $reservation->break_time ? Carbon::parse($reservation->break_time) : null;

                if ($time->between($startTime, $endTime->subMinute())) {
                    $isAvailable = false;
                    break;
                }

                if ($break_time) {
                    if ($time->between($endTime, $break_time->subMinute())) {
                        $isAvailable = false;
                        break;
                    }
                }

            }

            if ($isAvailable) {
                $key = $time->format('H:i');
                $availableReservations[$key] = $time->format('H:i');
            }
        }

        return $availableReservations;
    }

    private static function getNextReservation(int $machine_id, string $date, string $start_time)
    {
        $date = Carbon::parse($date);
        [$hour, $minute] = explode(':', $start_time);
        $start_time = $date->setTime((int)$hour, (int)$minute);

        return Reservation::query()->where('machine_id', $machine_id)
            ->where('start_time', '>', $start_time)
            ->orderBy('start_time')
            ->first();
    }

    public static function getNextReservationStartTime(int $machine_id, string $date, string $start_time): string
    {
        $nextReservation = self::getNextReservation($machine_id, $date, $start_time);

        if ($nextReservation) {
            return 'Next reservation starts at ' . Carbon::parse($nextReservation->start_time)->toDateTimeString();
        }

        return 'No upcoming reservations';
    }

    public static function getDurations(int $machine_id, string $date, string $start_time): array
    {
        if ($machine_id && $date && $start_time) {
            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $nextReservation = Reservation::query()->where('machine_id', $machine_id)
                ->where('start_time', '>', $start_time)
                ->orderBy('start_time')
                ->first();

            $maxDuration = $nextReservation ? $start_time->diffInMinutes($nextReservation->start_time) : 180;
        } else {
            $maxDuration = 180;
        }

        return collect([
            '15' => '15 minutes',
            '30' => '30 minutes',
            '45' => '45 minutes',
            '60' => '1 hour',
            '75' => '1 hour 15 minutes',
            '90' => '1 hour 30 minutes',
            '105' => '1 hour 45 minutes',
            '120' => '2 hours',
            '135' => '2 hours 15 minutes',
            '150' => '2 hours 30 minutes',
            '165' => '2 hours 45 minutes',
            '180' => '3 hours',
        ])->filter(function ($label, $minutes) use ($maxDuration) {
            return $minutes <= $maxDuration;
        })->toArray();
    }

    public static function getAvailableBreakDurations(int $machine_id, string $date, string $start_time, string $duration): array
    {
        if ($machine_id && $date && $start_time) {

            $nextReservation = self::getNextReservation($machine_id, $date, $start_time);

            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $end_time = $start_time->addMinutes((int)$duration);

            $maxDuration = $nextReservation ? $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time)) : 180;
        } else {
            $maxDuration = 180;
        }

        return collect([
            '15' => '15 minutes',
            '30' => '30 minutes',
            '45' => '45 minutes',
            '60' => '1 hour',
            '75' => '1 hour 15 minutes',
            '90' => '1 hour 30 minutes',
            '105' => '1 hour 45 minutes',
            '120' => '2 hours',
            '135' => '2 hours 15 minutes',
            '150' => '2 hours 30 minutes',
            '165' => '2 hours 45 minutes',
            '180' => '3 hours',
        ])->filter(function ($label, $minutes) use ($maxDuration) {
            return $minutes <= $maxDuration;
        })->toArray();
    }

    public static function disableBreaksInput(int $machine_id, string $date, string $start_time, string $duration): bool
    {
        $array = self::getAvailableBreakDurations($machine_id, $date, $start_time, $duration);

        return empty($array);
    }
}
