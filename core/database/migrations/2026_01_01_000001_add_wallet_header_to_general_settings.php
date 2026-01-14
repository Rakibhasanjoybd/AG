<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'wallet_images')) {
                $table->text('wallet_images')->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'wallet_image_effect')) {
                $table->string('wallet_image_effect', 50)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'wallet_images')) {
                $table->dropColumn('wallet_images');
            }
            if (Schema::hasColumn('general_settings', 'wallet_image_effect')) {
                $table->dropColumn('wallet_image_effect');
            }
        });
    }
};
