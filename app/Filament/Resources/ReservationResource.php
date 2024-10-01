<?php

namespace App\Filament\Resources;

use App\Enums\ReservationStatus;
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
                TextColumn::make('client.name')->label('Client')->searchable(),
                TextColumn::make('machine.name')->label('Machine'),
                TextColumn::make('assigned_user.name')->label('Assigned User'),
                TextColumn::make('operations.name')->label('Operations'),
                TextColumn::make('start_time')->label('Date and Start Time')->dateTime('D, d M Y H:i')->color(Color::Blue),
                TextColumn::make('end_time')->label('End Time')->time('H:i'),
                TextColumn::make('break_time')->label('Break Till')->time('H:i'),
                TextColumn::make('total_price')
                    ->formatStateUsing(fn($state) => number_format($state, 2, '.', ',') . ' MKD')
                    ->label('Total Price'),
                TextColumn::make('reservation_status')
                    ->icon(fn(Reservation $record): string => match ($record->getReservationStatusAttribute()) {
                        ReservationStatus::ONGOING => 'heroicon-o-minus-circle',
                        ReservationStatus::SCHEDULED => 'heroicon-o-clock',
                        ReservationStatus::FINISHED => 'heroicon-o-check-circle',
                        ReservationStatus::CANCELED => 'heroicon-o-x-circle',
                        ReservationStatus::PENDING_FINISH => 'heroicon-o-exclamation-circle',
                    })
                    ->color(fn(Reservation $record): string => match ($record->getReservationStatusAttribute()) {
                        ReservationStatus::ONGOING => 'warning',
                        ReservationStatus::SCHEDULED => 'success',
                        ReservationStatus::FINISHED => 'primary',
                        ReservationStatus::CANCELED => 'danger',
                        ReservationStatus::PENDING_FINISH => 'gray',
                    })
                    ->label('Status')
                    ->sortable()
            ])
//            ->filters([
//                SelectFilter::make('reservation_status')
//                    ->options([
//                        ReservationStatus::ONGOING->value => 'Ongoing',
//                        ReservationStatus::SCHEDULED->value => 'Scheduled',
//                        ReservationStatus::PENDING_FINISH->value => 'Pending Finish',
//                        ReservationStatus::FINISHED->value => 'Finished',
//                        ReservationStatus::CANCELED->value => 'Canceled',
//                    ])
//                    ->query(function (Builder $query, array $data) {
//                        $status = $data['value'];
//
//                        $currentDate = now()->timezone('GMT+2');
//
//                        if ($status === ReservationStatus::ONGOING->value) {
//                            $query->where('start_time', '<=', $currentDate)->where('end_time', '>=', $currentDate)
//                                ->where('status', '!=', ReservationStatus::CANCELED);
//                        } elseif ($status === ReservationStatus::SCHEDULED->value) {
//                            $query->where('start_time', '>=', $currentDate)
//                                ->where('status', '!=', ReservationStatus::CANCELED);
//                        } elseif ($status === ReservationStatus::PENDING_FINISH->value) {
//                            $query->where('end_time', '<=', $currentDate)
//                                ->where('status', '!=', ReservationStatus::CANCELED)
//                                ->where('status', '!=', ReservationStatus::FINISHED);
//                        } elseif ($status === ReservationStatus::FINISHED->value) {
//                            $query->where('status', ReservationStatus::FINISHED);
//                        } elseif ($status === ReservationStatus::CANCELED->value) {
//                            $query->where('status', ReservationStatus::CANCELED);
//                        }
//                    })
//                    ->label('Reservation Status'),
//
//            ])
            ->defaultSort(function (Builder $query) {
                $currentDate = now()->timezone('GMT+2');

                return $query
                    ->orderByRaw("CASE
                WHEN status = ? THEN 5
                WHEN start_time <= ? AND end_time >= ? THEN 1
                WHEN start_time >= ? THEN 2
                WHEN end_time <= ? THEN 3
                WHEN status = ? THEN 4
            END",
                        [
                            ReservationStatus::CANCELED->value,
                            $currentDate,
                            $currentDate,
                            $currentDate,
                            $currentDate,
                            ReservationStatus::FINISHED->value,
                        ]
                    )
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
}
