<?php

namespace App\Filament\App\Resources\ReservationResource\Components;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ReservationFilters
{
    public static function status(): SelectFilter
    {
        return SelectFilter::make('reservation_status')
            ->options([
                ReservationStatus::ONGOING->value => __('Ongoing'),
                ReservationStatus::SCHEDULED->value => __('Scheduled'),
                ReservationStatus::PENDING_FINISH->value => __('Pending Finish'),
                ReservationStatus::FINISHED->value => __('Finished'),
                ReservationStatus::CANCELED->value => __('Canceled')
            ])
            ->searchable()
            ->placeholder(__('Select status'))
            ->query(function (Builder $query, array $data) {
                $status = $data['value'];

                $currentDate = now()->timezone('GMT+2');

                if ($status === ReservationStatus::ONGOING->value) {
                    $query->where('start_time', '<=', $currentDate)->where('end_time', '>=', $currentDate)
                        ->where('status', '!=', ReservationStatus::CANCELED);
                } elseif ($status === ReservationStatus::SCHEDULED->value) {
                    $query->where('start_time', '>=', $currentDate)
                        ->where('status', '!=', ReservationStatus::CANCELED);
                } elseif ($status === ReservationStatus::PENDING_FINISH->value) {
                    $query->where('end_time', '<=', $currentDate)
                        ->where('status', '!=', ReservationStatus::CANCELED)
                        ->where('status', '!=', ReservationStatus::FINISHED);
                } elseif ($status === ReservationStatus::FINISHED->value) {
                    $query->where('status', ReservationStatus::FINISHED);
                } elseif ($status === ReservationStatus::CANCELED->value) {
                    $query->where('status', ReservationStatus::CANCELED);
                }
            })
            ->label(__('Reservation Status'));
    }

    public static function machine(): SelectFilter
    {
        return SelectFilter::make('machine')
            ->relationship('machine', 'name')
            ->options(Machine::all())
            ->searchable()
            ->label(__('Machine'));
    }

    public static function client(): SelectFilter
    {
        return SelectFilter::make('client')
            ->relationship('client', 'name')
            ->options(Client::all())
            ->searchable()
            ->placeholder(__('Select a client'))
            ->label(__('Client'));
    }

    public static function assigned_user(): SelectFilter
    {
        return SelectFilter::make('assigned_user')
            ->relationship('assigned_user', 'name')
            ->options(User::all())
            ->searchable()
            ->placeholder(__('Select a user'))
            ->label(__('Assigned User'));
    }

    public static function operations(): SelectFilter
    {
        return SelectFilter::make('operations')
            ->relationship('operations', 'name')
            ->options(Operation::all())
            ->multiple()
            ->searchable()
            ->placeholder(__('Select operations'))
            ->label(__('Operations'));
    }
    public static function fromDate(): Filter
    {
        return Filter::make('from_date')
            ->form([
                DatePicker::make('from_date')
                    ->label(__('From Date'))
                    ->placeholder(__('Select start date')),
            ])
            ->query(function (Builder $query, array $data) {
                if (!empty($data['from_date'])) {
                    $query->where('start_time', '>=', $data['from_date']);
                }
            })
            ->indicateUsing(function (array $data) {
                return !empty($data['from_date']) ? __('From Date: ') . \Carbon\Carbon::parse($data['from_date'])->format('Y-m-d') : null;
            });
    }

    public static function toDate(): Filter
    {
        return Filter::make('to_date')
            ->form([
                DatePicker::make('to_date')
                    ->label(__('To Date'))
                    ->placeholder(__('Select end date')),
            ])
            ->query(function (Builder $query, array $data) {
                if (!empty($data['to_date'])) {
                    $query->where('end_time', '<=', $data['to_date']);
                }
            })
            ->indicateUsing(function (array $data) {
                return !empty($data['to_date']) ? __('To Date: ') . \Carbon\Carbon::parse($data['to_date'])->format('Y-m-d') : null;
            });
    }

}
