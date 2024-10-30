<?php

namespace App\Filament\Resources;

use App\Enums\ReservationStatus;
use App\Filament\Components\ReservationTable;
use App\Filament\Components\ViewReservationForm;
use App\Filament\Resources\ReservationResource\Components\CreateReservationForm;
use App\Filament\Resources\ReservationResource\Components\EditReservationForm;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReservationResource extends Resource
{
    use Translatable;

    protected static ?string $model = Reservation::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
          Pages\ViewReservation::class,
          Pages\EditReservation::class,
        ]);
    }

    public static function canEdit(Model $record): bool
    {
        if ($record instanceof Reservation) {
            if ($record->getReservationStatusAttribute() === ReservationStatus::FINISHED ||
                $record->getReservationStatusAttribute() === ReservationStatus::CANCELED) {
                return false;
            } elseif ($record->user_id !== auth()->id() || $record->assigned_user_id !== auth()->id()) {
                return false;
            }

            return true;
        }

        return false;
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof Reservation) {
            if ($record->getReservationStatusAttribute() === ReservationStatus::FINISHED) {
                return false;
            } elseif ($record->user_id !== auth()->id() || $record->assigned_user_id !== auth()->id()) {
                return false;
            }

            return true;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema(
                    ViewReservationForm::form()
                )->columns(3)->columnSpan(2)->visible(fn($livewire) => $livewire instanceof Pages\ViewReservation),

                Section::make()->schema(
                    CreateReservationForm::form()
                )->visible(fn($livewire) => $livewire instanceof Pages\CreateReservation),

                Section::make()->schema(
                    EditReservationForm::form()
                )->columns(2)->visible(fn($livewire) => $livewire instanceof Pages\EditReservation)
            ]);
    }

    public static function table(Table $table): Table
    {
        return ReservationTable::make($table);
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

    public static function getNavigationGroup(): ?string
    {
        return __('Reservation Management');
    }

    public static function getModelLabel(): string
    {
        return __('Reservation');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Reservations');
    }
}
