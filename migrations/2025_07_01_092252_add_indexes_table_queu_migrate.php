<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('queue_monitor', function (Blueprint $table) {
            $table->index('job_uuid', 'queue_monitor_job_uuid');
            $table->index('status', 'queue_monitor_status');
            $table->index('name', 'queue_monitor_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('queue_monitor', function (Blueprint $table) {
            $table->dropIndex('queue_monitor_job_uuid');
            $table->dropIndex('queue_monitor_status');
            $table->dropIndex('queue_monitor_name');
        });
    }
};
