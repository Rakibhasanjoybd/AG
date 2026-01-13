<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Free User System settings to general_settings table.
     *
     * Free User System - Controls all settings for users without subscription:
     * - free_user_system_enabled: Enable/disable free user features
     * - free_user_can_earn_referral: Can free users earn referral commissions
     * - free_user_referral_level: Max referral levels for free users (0 = no commission)
     * - free_user_deposit_commission_percent: Deposit commission % for free users (per level)
     * - free_user_task_commission_percent: Task/PTC commission % for free users (per level)
     * - free_user_plan_commission_percent: Plan subscription commission % for free users (per level)
     * - free_user_daily_withdraw_limit: Daily withdrawal limit for free users
     * - free_user_min_withdraw: Minimum withdrawal for free users
     * - free_user_max_withdraw: Maximum withdrawal for free users
     * - free_user_ptc_limit: Daily PTC view limit for free users
     * - free_user_can_view_ptc: Can free users view PTC ads
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            // Free User System Master Switch
            if (!Schema::hasColumn('general_settings', 'free_user_system_enabled')) {
                $table->tinyInteger('free_user_system_enabled')->default(1)->after('referral_signup_referred_amount');
            }

            // Referral Commission Settings for Free Users
            if (!Schema::hasColumn('general_settings', 'free_user_can_earn_referral')) {
                $table->tinyInteger('free_user_can_earn_referral')->default(1);
            }
            if (!Schema::hasColumn('general_settings', 'free_user_referral_level')) {
                $table->tinyInteger('free_user_referral_level')->default(1); // Only level 1
            }

            // Commission percentages for free users (JSON format for multi-level)
            if (!Schema::hasColumn('general_settings', 'free_user_deposit_commission')) {
                $table->json('free_user_deposit_commission')->nullable(); // [{"level":1,"percent":4}]
            }
            if (!Schema::hasColumn('general_settings', 'free_user_task_commission')) {
                $table->json('free_user_task_commission')->nullable(); // [{"level":1,"percent":10}]
            }
            if (!Schema::hasColumn('general_settings', 'free_user_plan_commission')) {
                $table->json('free_user_plan_commission')->nullable(); // [{"level":1,"percent":10}]
            }

            // Withdrawal limits for free users
            if (!Schema::hasColumn('general_settings', 'free_user_daily_withdraw_limit')) {
                $table->decimal('free_user_daily_withdraw_limit', 28, 8)->default(100.00000000);
            }
            if (!Schema::hasColumn('general_settings', 'free_user_min_withdraw')) {
                $table->decimal('free_user_min_withdraw', 28, 8)->default(50.00000000);
            }
            if (!Schema::hasColumn('general_settings', 'free_user_max_withdraw')) {
                $table->decimal('free_user_max_withdraw', 28, 8)->default(500.00000000);
            }

            // PTC settings for free users
            if (!Schema::hasColumn('general_settings', 'free_user_can_view_ptc')) {
                $table->tinyInteger('free_user_can_view_ptc')->default(1);
            }
            if (!Schema::hasColumn('general_settings', 'free_user_ptc_limit')) {
                $table->integer('free_user_ptc_limit')->default(5);
            }
            if (!Schema::hasColumn('general_settings', 'free_user_ptc_earning')) {
                $table->decimal('free_user_ptc_earning', 28, 8)->default(0.50000000);
            }

            // Red Bag settings for free users
            if (!Schema::hasColumn('general_settings', 'free_user_can_claim_red_bag')) {
                $table->tinyInteger('free_user_can_claim_red_bag')->default(0);
            }
        });

        // Set default commission structure for free users
        $general = \App\Models\GeneralSetting::first();
        if ($general) {
            $general->free_user_deposit_commission = json_encode([
                ['level' => 1, 'percent' => 4]
            ]);
            $general->free_user_task_commission = json_encode([
                ['level' => 1, 'percent' => 10]
            ]);
            $general->free_user_plan_commission = json_encode([
                ['level' => 1, 'percent' => 10]
            ]);
            $general->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $columns = [
                'free_user_system_enabled',
                'free_user_can_earn_referral',
                'free_user_referral_level',
                'free_user_deposit_commission',
                'free_user_task_commission',
                'free_user_plan_commission',
                'free_user_daily_withdraw_limit',
                'free_user_min_withdraw',
                'free_user_max_withdraw',
                'free_user_can_view_ptc',
                'free_user_ptc_limit',
                'free_user_ptc_earning',
                'free_user_can_claim_red_bag'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('general_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
