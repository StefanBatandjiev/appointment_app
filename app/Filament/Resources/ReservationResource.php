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
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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
use Illuminate\Support\HtmlString;

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
                                    time_nanosleep(1, 0);

                                    Notification::make()
                                        ->title('Canceled Reservation')
                                        ->success()
                                        ->send();

                                } catch (\Exception $e) {

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

                                    Notification::make()
                                        ->title('Finished Reservation')
                                        ->success()
                                        ->send();
                                } catch (\Exception $e) {

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
                ])->columns(3)->columnSpan(2)->visible(fn($livewire) => $livewire instanceof Pages\ViewReservation),

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
            ->defaultSort(function (Builder $query) {
                $currentDate = now();

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
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
