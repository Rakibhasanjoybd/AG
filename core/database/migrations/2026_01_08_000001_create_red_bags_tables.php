<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Red Bag Configuration Table
        Schema::create('red_bags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Daily Red Bag');
            $table->decimal('min_amount', 16, 2)->default(0.50);
            $table->decimal('max_amount', 16, 2)->default(10.00);
            $table->integer('daily_limit')->default(1)->comment('How many times per day user can claim');
            $table->integer('new_user_bonus_count')->default(3)->comment('Extra red bags for new users');
            $table->integer('new_user_days')->default(7)->comment('Days considered as new user');
            $table->time('start_time')->default('08:00:00')->comment('Red bag available from');
            $table->time('end_time')->default('22:00:00')->comment('Red bag available until');
            $table->decimal('win_probability', 5, 2)->default(70.00)->comment('Percentage chance to win');
            $table->decimal('total_daily_budget', 16, 2)->default(1000.00)->comment('Max total daily payout');
            $table->decimal('spent_today', 16, 2)->default(0.00);
            $table->date('budget_reset_date')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('require_referral')->default(false)->comment('Must have referrals to claim');
            $table->integer('min_referrals')->default(0);
            $table->text('winning_message')->nullable();
            $table->text('losing_message')->nullable();
            $table->timestamps();
        });

        // Red Bag Claims Table - Track user claims with fraud prevention
        Schema::create('red_bag_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('red_bag_id');
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->boolean('is_winner')->default(false);
            $table->string('device_id')->nullable()->comment('Device fingerprint');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_hash')->nullable()->comment('Browser session fingerprint');
            $table->boolean('is_fraudulent')->default(false);
            $table->string('fraud_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('red_bag_id')->references('id')->on('red_bags')->onDelete('cascade');

            $table->index(['user_id', 'created_at']);
            $table->index(['device_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        // Device tracking for fraud prevention
        Schema::create('red_bag_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->unsignedBigInteger('first_user_id')->nullable();
            $table->integer('claim_count')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamps();

            $table->foreign('first_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('red_bag_devices');
        Schema::dropIfExists('red_bag_claims');
        Schema::dropIfExists('red_bags');
    }
};
