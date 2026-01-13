<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('free_user_step_commission_enabled')->default(0)->after('free_user_referral_level');
            $table->decimal('free_user_step_base_amount', 18, 8)->default(100)->after('free_user_step_commission_enabled');
            $table->decimal('free_user_step_increment', 18, 8)->default(100)->after('free_user_step_base_amount');
            $table->integer('free_user_step_max')->default(10)->after('free_user_step_increment');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'free_user_step_commission_enabled',
                'free_user_step_base_amount',
                'free_user_step_increment',
                'free_user_step_max'
            ]);
        });
    }
};
