<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE INDEX idx_queue_monitor_dates ON queue_monitor (started_at DESC, started_at_exact DESC);');
    }

    public function down()
    {
        DB::statement('DROP INDEX idx_queue_monitor_dates ON queue_monitor');
    }
};
