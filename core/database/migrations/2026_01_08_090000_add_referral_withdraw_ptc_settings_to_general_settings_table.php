<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'referral_premium_base_value')) {
                $table->decimal('referral_premium_base_value', 28, 8)->default(100.00000000);
            }

            if (!Schema::hasColumn('general_settings', 'non_premium_withdraw_limit')) {
                $table->decimal('non_premium_withdraw_limit', 28, 8)->default(1000.00000000);
            }

            if (!Schema::hasColumn('general_settings', 'ptc_enable_global')) {
                $table->tinyInteger('ptc_enable_global')->default(1);
            }

            if (!Schema::hasColumn('general_settings', 'ptc_max_unlock_level')) {
                $table->unsignedTinyInteger('ptc_max_unlock_level')->default(3);
            }
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'ptc_max_unlock_level')) {
                $table->dropColumn('ptc_max_unlock_level');
            }
            if (Schema::hasColumn('general_settings', 'ptc_enable_global')) {
                $table->dropColumn('ptc_enable_global');
            }
            if (Schema::hasColumn('general_settings', 'non_premium_withdraw_limit')) {
                $table->dropColumn('non_premium_withdraw_limit');
            }
            if (Schema::hasColumn('general_settings', 'referral_premium_base_value')) {
                $table->dropColumn('referral_premium_base_value');
            }
        });
    }
};
