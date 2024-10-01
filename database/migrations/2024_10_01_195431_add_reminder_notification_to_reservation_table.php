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
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('reminder_notification')->default(false);
            $table->boolean('pending_finish_notification')->default(false);
        });

        DB::table('reservations')->where('start_time', '<', now())->update([
            'reminder_notification' => true
        ]);

        DB::table('reservations')->where('status', '=', \App\Enums\ReservationStatus::FINISHED)->update([
            'pending_finish_notification' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['reminder_notification', 'pending_finish_notification']);
        });
    }
};
