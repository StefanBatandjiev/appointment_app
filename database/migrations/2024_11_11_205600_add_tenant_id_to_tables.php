<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tenant = \App\Models\Tenant::query()->firstOrCreate([
            'name' => 'Tenant Test',
            'slug' => 'tenant-test'
        ]);

        Schema::table('machines', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
        });

        DB::table('machines')->update(['tenant_id' => $tenant->id]);
        DB::table('operations')->update(['tenant_id' => $tenant->id]);
        DB::table('reservations')->update(['tenant_id' => $tenant->id]);
        DB::table('clients')->update(['tenant_id' => $tenant->id]);

        $existingUserIds = DB::table('users')->pluck('id');
        foreach ($existingUserIds as $userId) {
            DB::table('tenant_user')->insert([
                'tenant_id' => $tenant->id,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tenant = \App\Models\Tenant::query()->first();

        Schema::table('machines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        DB::table('tenant_user')->where('tenant_id', $tenant->id)->delete();
    }
};
