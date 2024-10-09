<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('calendar.slotMinTime', '08:00:00');
        $this->migrator->add('calendar.slotMaxTime', '20:00:00');
        $this->migrator->add('calendar.slotDuration', '00:15:00');
    }
};
