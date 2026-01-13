<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'hold_balance')) {
                $table->decimal('hold_balance', 28, 8)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('users', 'referral_commission_hold')) {
                $table->decimal('referral_commission_hold', 28, 8)->default(0)->after('hold_balance');
            }
            if (!Schema::hasColumn('users', 'upgrade_commission_hold')) {
                $table->decimal('upgrade_commission_hold', 28, 8)->default(0)->after('referral_commission_hold');
            }
            if (!Schema::hasColumn('users', 'ptc_commission_hold')) {
                $table->decimal('ptc_commission_hold', 28, 8)->default(0)->after('upgrade_commission_hold');
            }
            if (!Schema::hasColumn('users', 'fullname')) {
                $table->string('fullname')->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('users', 'is_premium')) {
                $table->tinyInteger('is_premium')->default(0)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hold_balance', 'referral_commission_hold', 'upgrade_commission_hold', 'ptc_commission_hold', 'fullname', 'is_premium']);
        });
    }
};
