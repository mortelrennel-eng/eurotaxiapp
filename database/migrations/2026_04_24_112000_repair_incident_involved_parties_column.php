<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('incident_involved_parties')) {
            // Check if 'incident_id' exists and 'driver_behavior_id' does not
            if (Schema::hasColumn('incident_involved_parties', 'incident_id') && !Schema::hasColumn('incident_involved_parties', 'driver_behavior_id')) {
                Schema::table('incident_involved_parties', function (Blueprint $table) {
                    // Manual rename for older MariaDB versions (XAMPP 10.4 etc)
                    DB::statement('ALTER TABLE incident_involved_parties CHANGE incident_id driver_behavior_id BIGINT UNSIGNED NOT NULL');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('incident_involved_parties')) {
            if (Schema::hasColumn('incident_involved_parties', 'driver_behavior_id')) {
                Schema::table('incident_involved_parties', function (Blueprint $table) {
                    // Manual rename back for older MariaDB versions
                    DB::statement('ALTER TABLE incident_involved_parties CHANGE driver_behavior_id incident_id BIGINT UNSIGNED NOT NULL');
                });
            }
        }
    }
};
