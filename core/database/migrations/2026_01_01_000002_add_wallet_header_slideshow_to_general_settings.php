<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'wallet_header_slideshow')) {
                $table->tinyInteger('wallet_header_slideshow')->default(1)->after('wallet_image_effect');
            }
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'wallet_header_slideshow')) {
                $table->dropColumn('wallet_header_slideshow');
            }
        });
    }
};
