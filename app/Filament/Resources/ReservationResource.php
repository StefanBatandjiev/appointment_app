<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationGroup = 'Reservation Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        $dateFormat = 'Y-m-d';

        return $form
            ->schema([
                Section::make()->schema([
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
                        ->createOptionUsing(function (array $data): int{
                            $client = Client::create([
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
                        ->live(),
                    Select::make('operation_id')
                        ->label('Operation')
                        ->options(Operation::all()->pluck('name', 'id'))
                        ->required(),
                    DatePicker::make('date')
                        ->minDate(now()->format($dateFormat))
                        ->maxDate(now()->addWeeks(2)->format($dateFormat))
                        ->required()
                        ->live(),
                    Select::make('start_time')
                        ->options(fn (Get $get) => (new ReservationService())->getAvailableTimesForDate($get('date')))
                        ->hidden(fn (Get $get) => ! $get('date'))
                        ->required()
                        ->searchable()
                        ->live(),
                    Select::make('duration')
                        ->label('Duration')
                        ->options(fn (Get $get) => (new ReservationService())->getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                        ->helperText(fn (Get $get) => (new ReservationService())->getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? ''))
                        ->hidden(fn (Get $get) => ! $get('start_time'))
                        ->required()
                        ->live(),
                    Select::make('break')
                        ->label('Break Time')
                        ->options(fn (Get $get) => (new ReservationService())->getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                        ->helperText('You can add a break time after the reservation')
                        ->hidden(fn (Get $get) => ! $get('duration'))
                        ->disabled(fn (Get $get) => (new ReservationService())->disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start_time') ?? '', $get('duration') ?? ''))
                ])->visible(fn($livewire) => $livewire instanceof Pages\CreateReservation),

                Section::make()->schema([
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
                        ->options(Operation::all()->pluck('name', 'id'))
                        ->required(),
                    TextInput::make('start_time')->disabled(),
                    TextInput::make('end_time')->disabled(),
                    TextInput::make('break_time')->disabled(),
                    Section::make('Change the reservation time')->schema([
                        DatePicker::make('date')
                            ->minDate(now()->format($dateFormat))
                            ->maxDate(now()->addWeeks(2)->format($dateFormat))
                            ->required()
                            ->live(),
                        Select::make('start')
                            ->options(fn (Get $get) => (new ReservationService())->getAvailableTimesForDate($get('date'), $get('id')))
                            ->hidden(fn (Get $get) => ! $get('date'))
                            ->required()
                            ->searchable()
                            ->live(),
                        Select::make('duration')
                            ->label('Duration')
                            ->options(fn (Get $get) => (new ReservationService())->getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                            ->helperText(fn (Get $get) => (new ReservationService())->getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? ''))
                            ->hidden(fn (Get $get) => ! $get('start'))
                            ->required()
                            ->live(),
                        Select::make('break')
                            ->label('Break Time')
                            ->options(fn (Get $get) => (new ReservationService())->getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                            ->helperText('You can add a break time after the reservation')
                            ->hidden(fn (Get $get) => ! $get('duration'))
                            ->disabled(fn (Get $get) => (new ReservationService())->disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))

                    ])
                ])->columns(2)->visible(fn($livewire) => $livewire instanceof Pages\EditReservation)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Created By User'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('machine.name')->label('Machine'),
                TextColumn::make('operation.name')->label('Operation'),
                TextColumn::make('start_time')->label('Date and Start Time')->dateTime('D, d M Y H:i')->color(Color::Blue),
                TextColumn::make('end_time')->label('End Time')->time('H:i')->Color(Color::Green),
                TextColumn::make('break_time')->label('Break Till')->time('H:i')->color(Color::Red),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
