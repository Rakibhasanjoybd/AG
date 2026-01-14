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
    public function up()
    {
        Schema::table('ptc_views', function (Blueprint $table) {
            if (!Schema::hasColumn('ptc_views', 'device_fingerprint')) {
                $table->string('device_fingerprint', 64)->nullable()->after('view_date');
            }
            if (!Schema::hasColumn('ptc_views', 'watch_time')) {
                $table->integer('watch_time')->nullable()->after('device_fingerprint');
            }
            if (!Schema::hasColumn('ptc_views', 'tab_switches')) {
                $table->tinyInteger('tab_switches')->default(0)->after('watch_time');
            }
            if (!Schema::hasColumn('ptc_views', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('tab_switches');
            }

            // Index for fraud detection queries
            if (Schema::hasColumn('ptc_views', 'device_fingerprint')) {
                $table->index('device_fingerprint');
            }
            if (Schema::hasColumn('ptc_views', 'ip_address')) {
                $table->index('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ptc_views', function (Blueprint $table) {
            $table->dropIndex(['device_fingerprint']);
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['device_fingerprint', 'watch_time', 'tab_switches', 'ip_address']);
        });
    }
};
