<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'country_restriction')) {
                $table->tinyInteger('country_restriction')->default(0)->after('code');
            }
            if (!Schema::hasColumn('general_settings', 'allowed_countries')) {
                $table->text('allowed_countries')->nullable()->after('country_restriction');
            }
            if (!Schema::hasColumn('general_settings', 'forced_country_code')) {
                $table->string('forced_country_code', 10)->nullable()->after('allowed_countries');
            }
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'forced_country_code')) {
                $table->dropColumn('forced_country_code');
            }
            if (Schema::hasColumn('general_settings', 'allowed_countries')) {
                $table->dropColumn('allowed_countries');
            }
            if (Schema::hasColumn('general_settings', 'country_restriction')) {
                $table->dropColumn('country_restriction');
            }
        });
    }
};
