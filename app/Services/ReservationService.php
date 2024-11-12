<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Operation;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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

    public static function editAction(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (isset($data['start']) && isset($data['duration'])) {
            $date = Carbon::parse($data['date']);

            [$hour, $minute] = explode(':', $data['start']);

            $startTime = $date->setTime((int)$hour, (int)$minute);
            $data['start_time'] = $startTime;

            $duration = (int)$data['duration'];

            $data['end_time'] = (clone $startTime)->addMinutes($duration);

            if (isset($data['break'])) {
                $data['break_time'] = (clone $data['end_time'])->addMinutes((int)$data['break']);
            } else {
                $data['break_time'] = null;
            }
        }

        unset($data['date']);
        unset($data['start']);
        unset($data['break']);
        unset($data['duration']);

        return $data;
    }
    public static function getAvailableTimesForDate(int $machine_id, string $date, int $reservationId = null): array
    {
        $date = Carbon::parse($date);
        $currentDate = now();
        $startPeriod = $date->copy()->setTime(8, 0);
        $endPeriod = $date->copy()->setTime(20, 0);

        if ($date->isToday() && $currentDate->hour >= 8) {
            $roundedMinutes = ceil($currentDate->minute / 5) * 5;

            if ($roundedMinutes == 60) {
                $roundedMinutes = 0;
                $currentDate->addHour();
            }

            $startPeriod = $date->copy()->setTime($currentDate->hour, $roundedMinutes);
        }

        $times = CarbonPeriod::create($startPeriod, '5 minutes', $endPeriod);
        $availableReservations = [];

        $reservations = Reservation::query()->where('machine_id', '=', $machine_id)->where('status', '!=', ReservationStatus::CANCELED)->whereDate('start_time', $date);

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

        return Reservation::query()
            ->where('status', '!=', ReservationStatus::CANCELED)
            ->where('machine_id', $machine_id)
            ->where('start_time', '>', $start_time)
            ->orderBy('start_time')
            ->first();
    }

    public static function getNextReservationStartTime(int $machine_id, string $date, string $start_time): string
    {
        $nextReservation = self::getNextReservation($machine_id, $date, $start_time);

        if ($nextReservation) {
            return 'Next reservation is at ' . Carbon::parse($nextReservation->start_time)->toDateTimeString();
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

            if ($nextReservation && $start_time->diffInMinutes($nextReservation->start_time) < 180) {
                $maxDuration = $start_time->diffInMinutes($nextReservation->start_time);
            } elseif ($start_time->diffInMinutes($date->copy()->setTime(20, 0), false) < 180) {
                $maxDuration = $start_time->diffInMinutes($date->copy()->setTime(20, 0), false);
            } else {
                $maxDuration = 180;
            }
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

//    public static function getDefaultDuration($start_time, $end_time) {
//        $duration = (string) Carbon::parse($start_time)->diffInMinutes(Carbon::parse($end_time));
//
//        $durations = [
//            '15' => '15 minutes',
//            '30' => '30 minutes',
//            '45' => '45 minutes',
//            '60' => '1 hour',
//            '75' => '1 hour 15 minutes',
//            '90' => '1 hour 30 minutes',
//            '105' => '1 hour 45 minutes',
//            '120' => '2 hours',
//            '135' => '2 hours 15 minutes',
//            '150' => '2 hours 30 minutes',
//            '165' => '2 hours 45 minutes',
//            '180' => '3 hours',
//        ];
//
//        return [
//            $duration => $durations[$duration]
//        ];
//    }

    public static function getAvailableBreakDurations(int $machine_id, string $date, string $start_time, string $duration): array
    {
        if ($machine_id && $date && $start_time) {

            $nextReservation = self::getNextReservation($machine_id, $date, $start_time);

            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $end_time = $start_time->addMinutes((int)$duration);

            if ($nextReservation && $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time)) < 180) {
                $maxDuration = $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time));
            } elseif ($end_time->diffInMinutes($date->copy()->setTime(20, 0), false) < 180) {
                $maxDuration = $end_time->diffInMinutes($date->copy()->setTime(20, 0), false);
            } else {
                $maxDuration = 180;
            }

        } else {
            $maxDuration = 180;
        }

        $options = [];
        for ($i = 5; $i <= $maxDuration; $i += 5) {
            $hours = intdiv($i, 60);
            $minutes = $i % 60;

            if ($hours > 0) {
                $label = $hours . ' hour' . ($hours > 1 ? 's' : '');
                if ($minutes > 0) {
                    $label .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                }
            } else {
                $label = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }

            $options[(string)$i] = $label;
        }

        return $options;
    }

    public static function disableBreaksInput(int $machine_id, string $date, string $start_time, string $duration): bool
    {
        $array = self::getAvailableBreakDurations($machine_id, $date, $start_time, $duration);

        return empty($array);
    }
}
