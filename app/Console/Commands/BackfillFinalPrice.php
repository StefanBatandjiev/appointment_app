<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class BackfillFinalPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:finalprice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reservations = Reservation::where('status', ReservationStatus::FINISHED)->get();

        foreach ($reservations as $reservation) {
            $reservation->final_price = $reservation->operations->sum('price');
            $reservation->save();
        }

        $this->info('Final price backfilled for finished reservations.');
    }
}
