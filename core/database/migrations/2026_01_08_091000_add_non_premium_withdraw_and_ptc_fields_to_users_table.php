<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'non_premium_withdraw_used')) {
                $table->decimal('non_premium_withdraw_used', 28, 8)->default(0);
            }

            if (!Schema::hasColumn('users', 'ptc_unlock_level')) {
                $table->unsignedTinyInteger('ptc_unlock_level')->default(0);
            }

            if (!Schema::hasColumn('users', 'ptc_income_locked')) {
                $table->tinyInteger('ptc_income_locked')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ptc_income_locked')) {
                $table->dropColumn('ptc_income_locked');
            }
            if (Schema::hasColumn('users', 'ptc_unlock_level')) {
                $table->dropColumn('ptc_unlock_level');
            }
            if (Schema::hasColumn('users', 'non_premium_withdraw_used')) {
                $table->dropColumn('non_premium_withdraw_used');
            }
        });
    }
};
