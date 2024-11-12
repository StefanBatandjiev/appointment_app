<?php

namespace App\Filament\App\Resources\ReservationResource\Components\Forms;

use App\Enums\ReservationStatus;
use App\Filament\App\Resources\ReservationResource\Components\Inputs\ReservationInputs;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;

class EditReservationForm
{
    public static function form($refreshRecords = null, $closeModal = null): array
    {
        return [
            Section::make(__('Reservation Editing Options'))
                ->description(fn(Reservation $reservation) => match ($reservation->getReservationStatusAttribute()) {
                    ReservationStatus::ONGOING => __('You can change the assigned user, operations, and reservation duration. You may also add break time after the reservation.'),
                    ReservationStatus::PENDING_FINISH => __('You can change the assigned user, operations, and reservation duration.'),
                    default => __('You can change the assigned user, operations, and reschedule the reservation'),
                })
                ->schema([
                    ReservationInputs::selectAssignedUser(),
                    ReservationInputs::selectMultipleOperations()->columnSpan(2),
                    ReservationInputs::selectDate()
                        ->minDate(function (Reservation $reservation) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::SCHEDULED) {
                                return now()->format('Y-m-d');
                            }
                            return null;
                        })
                        ->disabled(fn(Reservation $reservation) => $reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING)
                        ->dehydrated()
                        ->nullable()
                        ->columnSpan(3),
                    ReservationInputs::selectStartTime('start')
                        ->options(fn(Get $get) => ReservationService::getAvailableTimesForDate($get('machine_id'), $get('date'), $get('id')))
                        ->disabled(fn(Reservation $reservation) => $reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                            $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING)
                        ->dehydrated(),
                    ReservationInputs::selectDuration()
                        ->options(function (Reservation $reservation, Get $get) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                                $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                                return ReservationService::getDurations($reservation->machine_id, $reservation->start_time, Carbon::parse($reservation->start_time)->format('H:i'));
                            }

                            return ReservationService::getDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '');
                        })
                        ->nullable()
                        ->helperText(function (Reservation $reservation, Get $get) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                                $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                                return ReservationService::getNextReservationStartTime($reservation->machine_id, $reservation->start_time, Carbon::parse($reservation->start_time)->format('H:i'));
                            }

                            return ReservationService::getNextReservationStartTime($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '');
                        })
                        ->hidden(function (Reservation $reservation, Get $get) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                                $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {

                                return false;
                            }

                            return !$get('start');
                        })
                        ->afterStateHydrated(function (Reservation $reservation, Set $set) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH ||
                                $reservation->getReservationStatusAttribute() === ReservationStatus::ONGOING) {
                                $set('date', Carbon::parse($reservation->start_time)->format('Y-m-d'));
                                $set('start', Carbon::parse($reservation->start_time)->format('H:i'));
                            }
                        }),
                    ReservationInputs::selectBreakDuration()
                        ->options(fn(Get $get) => ReservationService::getAvailableBreakDurations($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                        ->hidden(function (Reservation $reservation, Get $get) {
                            if ($reservation->getReservationStatusAttribute() === ReservationStatus::PENDING_FINISH) {

                                return true;
                            }

                            return !$get('duration');
                        })
                        ->disabled(fn(Get $get) => ReservationService::disableBreaksInput($get('machine_id') ?? 0, $get('date') ?? '', $get('start') ?? '', $get('duration') ?? ''))
                ])->columns(3)->columnSpan(2)
        ];
    }
}
