<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Settings\CalendarSettings;
use App\Settings\ReservationSettings;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        $startPeriod = $date->copy()->setTimeFrom(app(CalendarSettings::class)->slotMinTime);
        $endPeriod = $date->copy()->setTimeFrom(app(CalendarSettings::class)->slotMaxTime);

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
            return __('Next reservation is at ') . Carbon::parse($nextReservation->start_time)->translatedFormat('D, d M Y H:i');
        }

        return __('No upcoming reservations');
    }

    public static function getDurations(int $machine_id, string $date, string $start_time): array
    {
        $calendarSlotMaxTime = app(CalendarSettings::class)->slotMaxTime;
        $reservationSettings = app(ReservationSettings::class);
        $reservationMaxDuration = $reservationSettings->reservationMaxDuration;
        $reservationTimeInterval = $reservationSettings->reservationTimeInterval;

        if ($machine_id && $date && $start_time) {
            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $nextReservation = Reservation::query()->where('machine_id', $machine_id)
                ->where('start_time', '>', $start_time)
                ->orderBy('start_time')
                ->first();

            if ($nextReservation && $start_time->diffInMinutes($nextReservation->start_time) < $reservationMaxDuration) {
                $maxDuration = $start_time->diffInMinutes($nextReservation->start_time);
            } elseif ($start_time->diffInMinutes($date->copy()->setTimeFrom($calendarSlotMaxTime)) < $reservationMaxDuration) {
                $maxDuration = $start_time->diffInMinutes($date->copy()->setTimeFrom($calendarSlotMaxTime));
            } else {
                $maxDuration = $reservationMaxDuration;
            }
        } else {
            $maxDuration = $reservationMaxDuration;
        }

        $durations = collect();

        for ($i = $reservationTimeInterval; $i <= $maxDuration; $i += $reservationTimeInterval) {
            $durationLabel = $i . __(' minutes');

            if ($i >= 60) {
                $hours = floor($i / 60);
                $minutes = $i % 60;
                if ($minutes == 0) {
                    $durationLabel = "{$hours} " . __('hour') . ($hours > 1 ? __('s') : '');
                } else {
                    $durationLabel = "{$hours} " . __('hour') . ($hours > 1 ? __('s') : '') . " {$minutes}" . ($minutes > 1 ? __(' minutes') : __(' minute'));
                }
            }

            $durations->put($i, $durationLabel);
        }

        return $durations->toArray();
    }
    public static function getAvailableBreakDurations(int $machine_id, string $date, string $start_time, string $duration): array
    {
        $calendarSlotMaxTime = app(CalendarSettings::class)->slotMaxTime;
        $reservationSettings = app(ReservationSettings::class);
        $breakMaxDuration = $reservationSettings->breakMaxDuration;
        $breakTimeInterval = $reservationSettings->breakTimeInterval;

        if ($machine_id && $date && $start_time) {

            $nextReservation = self::getNextReservation($machine_id, $date, $start_time);

            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $end_time = $start_time->addMinutes((int)$duration);

            if ($nextReservation && $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time)) < $breakMaxDuration) {
                $maxDuration = $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time));
            } elseif ($end_time->diffInMinutes($date->copy()->setTimeFrom($calendarSlotMaxTime)) < $breakMaxDuration) {
                $maxDuration = $end_time->diffInMinutes($date->copy()->setTimeFrom($calendarSlotMaxTime));
            } else {
                $maxDuration = $breakMaxDuration;
            }

        } else {
            $maxDuration = $breakMaxDuration;
        }

        $options = [];
        for ($i = $breakTimeInterval; $i <= $maxDuration; $i += $breakTimeInterval) {
            $hours = intdiv($i, 60);
            $minutes = $i % 60;

            if ($hours > 0) {
                $label = $hours . ' ' . __('hour') . ($hours > 1 ? __('s') : '');
                if ($minutes > 0) {
                    $label .= ' ' . $minutes . ($minutes > 1 ? __(' minutes') : __(' minute'));
                }
            } else {
                $label = $minutes . ($minutes > 1 ? __(' minutes') : __(' minute'));
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
