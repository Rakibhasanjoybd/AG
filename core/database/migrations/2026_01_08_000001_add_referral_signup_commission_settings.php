<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds two-side referral commission settings for account registration.
     * - referral_signup_commission: Enable/disable the feature (0/1)
     * - referral_signup_referrer_amount: Amount for referrer (default 10)
     * - referral_signup_referred_amount: Amount for referred user (default 10)
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'referral_signup_commission')) {
                $table->tinyInteger('referral_signup_commission')->default(1);
            }
            if (!Schema::hasColumn('general_settings', 'referral_signup_referrer_amount')) {
                $table->decimal('referral_signup_referrer_amount', 28, 8)->default(10.00000000);
            }
            if (!Schema::hasColumn('general_settings', 'referral_signup_referred_amount')) {
                $table->decimal('referral_signup_referred_amount', 28, 8)->default(10.00000000);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'referral_signup_commission',
                'referral_signup_referrer_amount',
                'referral_signup_referred_amount'
            ]);
        });
    }
};
