<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Plans withdrawal settings
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'anytime_withdraw_limit')) {
                $table->integer('anytime_withdraw_limit')->default(5)->comment('Number of anytime withdrawals allowed');
            }
            if (!Schema::hasColumn('plans', 'weekly_withdraw_day')) {
                $table->unsignedTinyInteger('weekly_withdraw_day')->default(0)->comment('Day of week for weekly withdrawal (0=Sunday, 1=Monday, etc.)');
            }
            if (!Schema::hasColumn('plans', 'weekly_withdraw_enabled')) {
                $table->unsignedTinyInteger('weekly_withdraw_enabled')->default(1)->comment('Enable weekly withdrawal after anytime limit exhausted');
            }
        });

        // Users withdrawal usage tracking
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'anytime_withdraw_used')) {
                $table->integer('anytime_withdraw_used')->default(0)->comment('Anytime withdrawals used by user');
            }
            if (!Schema::hasColumn('users', 'last_weekly_withdraw')) {
                $table->date('last_weekly_withdraw')->nullable()->comment('Date of last weekly withdrawal');
            }
            if (!Schema::hasColumn('users', 'plan_purchase_date')) {
                $table->dateTime('plan_purchase_date')->nullable()->comment('Date when current plan was purchased');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'plan_purchase_date')) {
                $table->dropColumn('plan_purchase_date');
            }
            if (Schema::hasColumn('users', 'last_weekly_withdraw')) {
                $table->dropColumn('last_weekly_withdraw');
            }
            if (Schema::hasColumn('users', 'anytime_withdraw_used')) {
                $table->dropColumn('anytime_withdraw_used');
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'weekly_withdraw_enabled')) {
                $table->dropColumn('weekly_withdraw_enabled');
            }
            if (Schema::hasColumn('plans', 'weekly_withdraw_day')) {
                $table->dropColumn('weekly_withdraw_day');
            }
            if (Schema::hasColumn('plans', 'anytime_withdraw_limit')) {
                $table->dropColumn('anytime_withdraw_limit');
            }
        });
    }
};
