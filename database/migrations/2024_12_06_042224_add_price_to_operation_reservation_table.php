<?php

use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operation_reservation', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable()->after('operation_id');
        });

        $reservations = Reservation::with('operations')->get();

        foreach ($reservations as $reservation) {
            foreach ($reservation->operations as $operation) {
                DB::table('operation_reservation')
                    ->where('reservation_id', $reservation->id)
                    ->where('operation_id', $operation->id)
                    ->update(['price' => $operation->price]);
            }

            $reservation->load('operations');

            $reservation->final_price = $reservation->operations->sum(fn($operation) => $operation->pivot->price);
            $reservation->save();
        }
    }
};
