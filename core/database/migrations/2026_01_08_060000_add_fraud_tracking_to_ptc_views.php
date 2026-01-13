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
            $table->string('device_fingerprint', 64)->nullable()->after('view_date');
            $table->integer('watch_time')->nullable()->after('device_fingerprint');
            $table->tinyInteger('tab_switches')->default(0)->after('watch_time');
            $table->string('ip_address', 45)->nullable()->after('tab_switches');

            // Index for fraud detection queries
            $table->index('device_fingerprint');
            $table->index('ip_address');
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
