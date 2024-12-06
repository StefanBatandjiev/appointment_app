<?php

namespace App\Filament\App\Resources;

use App\Enums\ReservationStatus;
use App\Filament\App\Resources\ReservationResource\Components\Forms\CreateReservationForm;
use App\Filament\App\Resources\ReservationResource\Components\Forms\EditReservationForm;
use App\Filament\App\Resources\ReservationResource\Components\Forms\ViewReservationForm;
use App\Filament\App\Resources\ReservationResource\Components\ReservationFilters;
use App\Filament\App\Resources\ReservationResource\Components\ReservationTable;
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
          \App\Filament\App\Resources\ReservationResource\Pages\ViewReservation::class,
          \App\Filament\App\Resources\ReservationResource\Pages\EditReservation::class,
        ]);
    }

    public static function canEdit(Model $record): bool
    {
        if ($record instanceof Reservation) {
            if ($record->getReservationStatusAttribute() === ReservationStatus::FINISHED ||
                $record->getReservationStatusAttribute() === ReservationStatus::CANCELED) {
                return false;
            }
//            elseif ($record->user_id === auth()->id()) {
//                return true;
//            } elseif ($record->assigned_user_id === auth()->id()) {
//                return true;
//            }
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof Reservation) {
            if ($record->getReservationStatusAttribute() === ReservationStatus::FINISHED) {
                return false;
            }
//            elseif ($record->user_id !== auth()->id() || $record->assigned_user_id !== auth()->id()) {
//                return false;
//            }
        }

        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema(
                    ViewReservationForm::form()
                )->columns(['sm' => 2, 'md' => 4])->columnSpan(2)->visible(fn($livewire) => $livewire instanceof \App\Filament\App\Resources\ReservationResource\Pages\ViewReservation),

                Section::make()->schema(
                    CreateReservationForm::form()
                )->visible(fn($livewire) => $livewire instanceof \App\Filament\App\Resources\ReservationResource\Pages\CreateReservation),

                Section::make()->schema(
                    EditReservationForm::form()
                )->columns(2)->visible(fn($livewire) => $livewire instanceof \App\Filament\App\Resources\ReservationResource\Pages\EditReservation)
            ]);
    }

    public static function table(Table $table): Table
    {
        return ReservationTable::make($table)
                ->filters([
                    ReservationFilters::fromDate(),
                    ReservationFilters::toDate(),
                    ReservationFilters::client(),
                    ReservationFilters::machine(),
                    ReservationFilters::operations(),
                    ReservationFilters::assigned_user()
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
            'index' => \App\Filament\App\Resources\ReservationResource\Pages\ListReservations::route('/'),
            'create' => \App\Filament\App\Resources\ReservationResource\Pages\CreateReservation::route('/create'),
            'view' => \App\Filament\App\Resources\ReservationResource\Pages\ViewReservation::route('/{record}'),
            'edit' => \App\Filament\App\Resources\ReservationResource\Pages\EditReservation::route('/{record}/edit'),
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
