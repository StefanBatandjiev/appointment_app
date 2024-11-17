<?php

namespace App\Filament\App\Resources\ReservationResource\Components\Inputs;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\User;
use App\Services\ReservationService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class ReservationInputs
{
    public static function selectClient(): Select
    {
        return Select::make('client_id')
            ->label(__('Client'))
            ->placeholder(__('Select a client'))
            ->options(Client::all()->pluck('name', 'id'))
            ->searchable()
            ->noSearchResultsMessage(__('No clients match your search.'))
            ->loadingMessage(__('Loading clients...'))
            ->searchPrompt(__('Start typing to search...'))
            ->suffixIcon('heroicon-o-user')
            ->suffixIconColor('primary')
            ->required()
            ->createOptionForm([
                TextInput::make('name')
                    ->label(__('Client Name'))
                    ->suffixIcon('heroicon-o-user')
                    ->suffixIconColor('primary')
                    ->required(),
                TextInput::make('email')
                    ->label(__('Client Email'))
                    ->email()
                    ->unique(Client::class, 'email', ignoreRecord: true)
                    ->suffixIcon('heroicon-o-envelope')
                    ->suffixIconColor('primary'),
                TextInput::make('telephone')
                    ->label(__('Client Telephone'))
                    ->unique(Client::class, 'telephone', ignoreRecord: true)
                    ->nullable()
                    ->suffixIcon('heroicon-o-phone')
                    ->suffixIconColor('primary')
            ])
            ->createOptionUsing(function (array $data): int {
                $client = Client::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'telephone' => $data['telephone'],
                ]);

                return $client->id;
            })
            ->columnSpan(3);
    }

    public static function selectMachine(): Select
    {
        return Select::make('machine_id')
            ->label(__('Machine'))
            ->placeholder(__('Select a machine'))
            ->searchable()
            ->noSearchResultsMessage(__('No machines match your search.'))
            ->loadingMessage(__('Loading machines...'))
            ->searchPrompt(__('Start typing to search...'))
            ->options(Machine::all()->pluck('name', 'id'))
            ->suffixIcon('heroicon-o-cog')
            ->suffixIconColor('primary')
            ->required()
            ->live()
            ->reactive()
            ->afterStateUpdated(function (callable $set) {
                $set('operations', null);
                $set('date', null);
                $set('start_time', null);
                $set('duration', null);
                $set('break', null);
            })
            ->columnSpan(2);
    }

    public static function selectAssignedUser(): Select
    {
        return Select::make('assigned_user_id')
            ->label(__('Assigned User'))
            ->placeholder(__('Select a user to assign'))
            ->searchable()
            ->noSearchResultsMessage(__('No users match your search.'))
            ->loadingMessage(__('Loading users...'))
            ->searchPrompt(__('Start typing to search...'))
            ->options(
                User::query()->
                where('is_admin', false)
                    ->get()->pluck('name', 'id')
            )
            ->suffixIcon('heroicon-o-user')
            ->suffixIconColor('primary')
            ->nullable()
            ->columnSpan(1);
    }

    public static function selectMultipleOperations($relationship = false): Select
    {
        if ($relationship) {
            $select = Select::make('operations')->relationship('operations', 'name');
        } else {
            $select = Select::make('operations');
        }

        return $select
            ->label(__('Operations'))
            ->multiple()
            ->placeholder(__('Select operations'))
            ->searchable()
            ->noSearchResultsMessage(__('No operations match your search.'))
            ->loadingMessage(__('Loading operations...'))
            ->searchingMessage(__('Searching operations...'))
            ->suffixIcon('heroicon-o-queue-list')
            ->suffixIconColor('primary')
            ->required()
            ->hidden(fn(Get $get) => !$get('machine_id'))
            ->options(
                Operation::all()
                    ->mapWithKeys(fn($operation) => [
                        $operation->id => "{$operation->name} - {$operation->price} MKD"
                    ])
                    ->lazy()
            )
            ->options(
                fn(Get $get) => Operation::query()
                    ->whereHas('machines', function ($query) use ($get) {
                        $query->where('machine_id', $get('machine_id'));
                    })
                    ->get()
                    ->mapWithKeys(fn($operation) => [
                        $operation->id => "{$operation->name} - {$operation->price}" . __(' MKD')
                    ])
            )
            ->createOptionForm([
                TextInput::make('name')
                    ->label(__('Operation Name'))
                    ->required()
                    ->suffixIcon('heroicon-o-bars-2')
                    ->suffixIconColor('primary'),
                Select::make('Machines')
                    ->options(Machine::all()->pluck('name', 'id'))
                    ->placeholder(__('Select a machine'))
                    ->label(__('Machines'))
                    ->suffixIcon('heroicon-o-cog')
                    ->suffixIconColor('primary')
                    ->multiple(),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->required()
                    ->suffixIcon('heroicon-o-banknotes')
                    ->suffixIconColor('primary'),
                ColorPicker::make('color')
                    ->label(__('Color'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('Operation Description'))
                    ->autosize()
                    ->columnSpan(2),
            ])
            ->createOptionUsing(function (array $data): int {
                $operation = Operation::query()->create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'color' => $data['color'],
                    'price' => $data['price'],
                ]);
                $machines = is_array($data['Machines']) ? $data['Machines'] : [$data['Machines']];

                $operation->machines()->attach($machines);

                return $operation->id;
            })->live()
            ->columnSpan(3);
    }

    public static function selectDate(): DatePicker
    {
        return DatePicker::make('date')
            ->label(__('Reservation Date'))
            ->minDate(now()->format('Y-m-d'))
            ->hidden(fn(Get $get) => !$get('operations'))
            ->suffixIcon('heroicon-o-calendar-date-range')
            ->suffixIconColor('primary')
            ->required()
            ->live()
            ->afterStateUpdated(function (callable $set) {
                $set('start_time', null);
                $set('duration', null);
                $set('break', null);
            })
            ->columnSpan(3);
    }

    public static function selectStartTime($name = 'start_time'): Select
    {
        return Select::make($name)
            ->label(__('Start Time'))
            ->placeholder(__('Choose a start time'))
            ->searchable()
            ->noSearchResultsMessage(__('No available times found for your search.'))
            ->loadingMessage(__('Retrieving available times...'))
            ->searchPrompt(__('Type to find a time slot...'))
            ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date')))
            ->hidden(fn(Get $get) => !$get('date'))
            ->suffixIcon('heroicon-o-clock')
            ->suffixIconColor('primary')
            ->required()
            ->live()
            ->afterStateUpdated(function (callable $set) {
                $set('duration', null);
                $set('break', null);
            });
    }

    public static function selectDuration(): Select
    {
        return Select::make('duration')
            ->label(__('Duration'))
            ->placeholder(__('Choose duration'))
            ->searchable()
            ->noSearchResultsMessage(__('No available durations found for your search.'))
            ->loadingMessage(__('Retrieving available durations...'))
            ->searchPrompt(__('Type to find a duration...'))
            ->options(fn(Get $get) => ReservationService::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
            ->helperText(fn(Get $get) => ReservationService::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
            ->hidden(fn(Get $get) => !$get('start_time'))
            ->suffixIcon('heroicon-o-clock')
            ->suffixIconColor('primary')
            ->required()
            ->live()
            ->afterStateUpdated(function (callable $set) {
                $set('break', null);
            });
    }

    public static function selectBreakDuration(): Select
    {
        return Select::make('break')
            ->label(__('Break Time'))
            ->placeholder(__('Choose break duration'))
            ->searchable()
            ->noSearchResultsMessage(__('No available break durations found for your search.'))
            ->loadingMessage(__('Retrieving available break durations...'))
            ->searchPrompt(__('Type to find a break duration...'))
            ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
            ->helperText(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? '')? '' : __('You can add a break time after the reservation'))
            ->hidden(fn(Get $get) => !$get('duration'))
            ->suffixIcon('heroicon-o-clock')
            ->suffixIconColor('primary')
            ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''));
    }
}
