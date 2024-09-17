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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
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
        return $form
            ->schema([
                Section::make()->schema(
                    ReservationService::createForm()
                )->visible(fn($livewire) => $livewire instanceof Pages\CreateReservation),

                Section::make()->schema(
                    ReservationService::editForm()
                )->columns(2)->visible(fn($livewire) => $livewire instanceof Pages\EditReservation)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Created By User'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('machine.name')->label('Machine'),
                TextColumn::make('operations.name')->label('Operations'),
                TextColumn::make('start_time')->label('Date and Start Time')->dateTime('D, d M Y H:i')->color(Color::Blue),
                TextColumn::make('end_time')->label('End Time')->time('H:i'),
                TextColumn::make('break_time')->label('Break Till')->time('H:i'),
                TextColumn::make('total_price')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ',') . ' MKD')
                    ->label('Total Price'),
                TextColumn::make('reservation_status')
                    ->icon(fn(string $state): string => match ($state) {
                        'Ongoing' => 'heroicon-o-minus-circle',
                        'Scheduled' => 'heroicon-o-clock',
                        'Finished' => 'heroicon-o-check-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Ongoing' => 'warning',
                        'Scheduled' => 'success',
                        'Finished' => 'danger',
                        default => 'gray',
                    })
                    ->label('Status')
                    ->getStateUsing(fn(Reservation $record) => self::getReservationStatus($record))
                    ->extraAttributes(['class' => 'flex items-center']),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Ongoing' => 'Ongoing',
                        'Scheduled' => 'Scheduled',
                        'Finished' => 'Finished',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'Ongoing') {
                            $query->where('start_time', '<=', now())
                                ->where('end_time', '>=', now());
                        } elseif ($data['value'] === 'Scheduled') {
                            $query->where('start_time', '>', now());
                        } elseif ($data['value'] === 'Finished') {
                            $query->where('end_time', '<', now());
                        }
                    })
                    ->label('Reservation Status'),
            ])
            ->defaultSort(function (Builder $query) {
                $now = now()->timezone('GMT+2');

                return $query
                    ->orderByRaw("CASE
                                WHEN start_time <= ? AND end_time >= ? THEN 1
                                WHEN start_time > ? THEN 2
                                WHEN end_time < ? THEN 3
                              END", [$now, $now, $now, $now])
                    ->orderBy('start_time');
            })
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

    protected static function getReservationStatus(Reservation $reservation): string
    {
        $currentDate = now()->timezone('GMT+2');
        if ($currentDate->between($reservation->start_time, $reservation->end_time)) {
            return 'Ongoing';
        } elseif ($currentDate->lessThan($reservation->start_time)) {
            return 'Scheduled';
        } else {
            return 'Finished';
        }
    }
}
