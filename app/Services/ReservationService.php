<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Core\DateTime;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    public function getAvailableTimesForDate(string $date, int $reservationId = null): array
    {
        $date = Carbon::parse($date);
        $startPeriod = $date->copy()->setTime(8, 0);
        $endPeriod = $date->copy()->setTime(20, 0);

        $times = CarbonPeriod::create($startPeriod, '5 minutes', $endPeriod);
        $availableReservations = [];

        $reservations = Reservation::query()->whereDate('start_time', $date);

        if($reservationId) {
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

                Log::info('Break time:' . $reservation->break_time);

                if ($time->between($startTime, $endTime->subMinute())) {
                    $isAvailable = false;
                    break;
                }

                if($break_time) {
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

    private function getNextReservation(int $machine_id, string $date, string $start_time)
    {
        $date = Carbon::parse($date);
        [$hour, $minute] = explode(':', $start_time);
        $start_time = $date->setTime((int)$hour, (int)$minute);

        return Reservation::query()->where('machine_id', $machine_id)
            ->where('start_time', '>', $start_time)
            ->orderBy('start_time', 'asc')
            ->first();
    }

    public function getNextReservationStartTime(int $machine_id, string $date, string $start_time): string
    {
        $nextReservation = $this->getNextReservation($machine_id, $date, $start_time);

        if ($nextReservation) {
            return 'Next reservation starts at ' . Carbon::parse($nextReservation->start_time)->toDateTimeString();
        }

        return 'No upcoming reservations';
    }

    public function getDurations(int $machine_id, string $date, string $start_time): array
    {
        if ($machine_id && $date && $start_time) {
            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $nextReservation = Reservation::query()->where('machine_id', $machine_id)
                ->where('start_time', '>', $start_time)
                ->orderBy('start_time', 'asc')
                ->first();

            $maxDuration = $nextReservation ? $start_time->diffInMinutes($nextReservation->start_time) : 180;
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

    public function getAvailableBreakDurations(int $machine_id, string $date, string $start_time, string $duration): array
    {
        if ($machine_id && $date && $start_time) {

            $nextReservation = $this->getNextReservation($machine_id, $date, $start_time);

            $date = Carbon::parse($date);
            [$hour, $minute] = explode(':', $start_time);
            $start_time = $date->setTime((int)$hour, (int)$minute);

            $end_time = $start_time->addMinutes((int)$duration);

            $maxDuration = $nextReservation ? $end_time->diffInMinutes(Carbon::parse($nextReservation->start_time)) : 180;
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

    public function disableBreaksInput(int $machine_id, string $date, string $start_time, string $duration)
    {
        $array = $this->getAvailableBreakDurations($machine_id, $date, $start_time, $duration);

        return empty($array);
    }
}
