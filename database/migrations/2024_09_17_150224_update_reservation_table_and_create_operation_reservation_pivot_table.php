<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('operation_reservation')) {
            Schema::create('operation_reservation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
                $table->foreignId('operation_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }

        DB::table('reservations')->whereNotNull('operation_id')->orderBy('id')->each(function ($reservation) {
            DB::table('operation_reservation')->insert([
                'reservation_id' => $reservation->id,
                'operation_id'   => $reservation->operation_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'operation_id')) {
                $table->dropForeign(['operation_id']);
                $table->dropColumn('operation_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->constrained()->onDelete('cascade');
        });

        $reservations = DB::table('operation_reservation')->get();

        foreach ($reservations as $reservation) {
            DB::table('reservations')->where('id', $reservation->reservation_id)->update([
                'operation_id' => $reservation->operation_id,
            ]);
        }

        Schema::dropIfExists('operation_reservation');
    }
};
