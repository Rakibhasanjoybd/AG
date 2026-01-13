<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hold Wallet Achievement System Migration
     *
     * Achievement Levels:
     * - Level 1 (1-49 referrals): 30 days wait for transfer
     * - Level 2 (50-199 referrals): 15 days wait for transfer
     * - Level 3 (200+ referrals): Instant transfer anytime
     */
    public function up()
    {
        // Add achievement tracking columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_referrals_count')) {
                $table->unsignedInteger('total_referrals_count')->default(0)->after('ptc_commission_hold');
            }
            if (!Schema::hasColumn('users', 'achievement_level')) {
                $table->unsignedTinyInteger('achievement_level')->default(1)->after('total_referrals_count');
            }
            if (!Schema::hasColumn('users', 'last_transfer_date')) {
                $table->date('last_transfer_date')->nullable()->after('achievement_level');
            }
        });

        // Add achievement level to hold wallet transactions for tracking
        Schema::table('hold_wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('hold_wallet_transactions', 'achievement_level_at_creation')) {
                $table->unsignedTinyInteger('achievement_level_at_creation')->default(1)->after('available_date');
            }
            if (!Schema::hasColumn('hold_wallet_transactions', 'hold_days')) {
                $table->unsignedInteger('hold_days')->default(30)->after('achievement_level_at_creation');
            }
            if (!Schema::hasColumn('hold_wallet_transactions', 'transfer_fee')) {
                $table->decimal('transfer_fee', 28, 8)->default(0)->after('hold_days');
            }
        });

        // Create hold wallet settings table
        if (!Schema::hasTable('hold_wallet_settings')) {
            Schema::create('hold_wallet_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });

            // Insert default settings
            DB::table('hold_wallet_settings')->insert([
                // Achievement Level Thresholds
                ['key' => 'level_1_min_referrals', 'value' => '1', 'description' => 'Minimum referrals for Level 1', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'level_1_max_referrals', 'value' => '49', 'description' => 'Maximum referrals for Level 1', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'level_1_hold_days', 'value' => '30', 'description' => 'Hold days for Level 1', 'created_at' => now(), 'updated_at' => now()],

                ['key' => 'level_2_min_referrals', 'value' => '50', 'description' => 'Minimum referrals for Level 2', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'level_2_max_referrals', 'value' => '199', 'description' => 'Maximum referrals for Level 2', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'level_2_hold_days', 'value' => '15', 'description' => 'Hold days for Level 2', 'created_at' => now(), 'updated_at' => now()],

                ['key' => 'level_3_min_referrals', 'value' => '200', 'description' => 'Minimum referrals for Level 3', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'level_3_hold_days', 'value' => '0', 'description' => 'Hold days for Level 3 (0 = instant)', 'created_at' => now(), 'updated_at' => now()],

                // Commission Split Settings
                ['key' => 'instant_percent', 'value' => '40', 'description' => 'Instant transfer percentage', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'hold_percent', 'value' => '60', 'description' => 'Hold percentage', 'created_at' => now(), 'updated_at' => now()],

                // Transfer Fee Settings
                ['key' => 'transfer_fee_type', 'value' => 'percent', 'description' => 'Fee type: fixed or percent', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'transfer_fee_amount', 'value' => '2', 'description' => 'Transfer fee amount (fixed BDT or percentage)', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'min_transfer_amount', 'value' => '100', 'description' => 'Minimum amount to transfer', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Create hold wallet transfer logs table
        if (!Schema::hasTable('hold_wallet_transfers')) {
            Schema::create('hold_wallet_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->decimal('amount', 28, 8)->default(0);
                $table->decimal('fee', 28, 8)->default(0);
                $table->decimal('net_amount', 28, 8)->default(0);
                $table->unsignedTinyInteger('achievement_level')->default(1);
                $table->unsignedInteger('referral_count_at_transfer')->default(0);
                $table->string('trx')->nullable();
                $table->text('details')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_referrals_count', 'achievement_level', 'last_transfer_date']);
        });

        Schema::table('hold_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['achievement_level_at_creation', 'hold_days', 'transfer_fee']);
        });

        Schema::dropIfExists('hold_wallet_settings');
        Schema::dropIfExists('hold_wallet_transfers');
    }
};
