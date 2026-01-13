<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Unique referral code for each user (4-5 digit numeric)
            $table->string('referral_code', 10)->unique()->nullable()->after('username');
            
            // Withdrawal PIN (4-6 digit)
            $table->string('withdrawal_pin', 255)->nullable()->after('password');
        });

        // Generate referral codes for existing users
        $users = \App\Models\User::whereNull('referral_code')->get();
        foreach ($users as $user) {
            $user->referral_code = $this->generateUniqueReferralCode();
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_code', 'withdrawal_pin']);
        });
    }

    /**
     * Generate unique 4-5 digit numeric referral code
     */
    private function generateUniqueReferralCode()
    {
        do {
            // Generate random 4-5 digit number
            $code = str_pad(random_int(1000, 99999), 4, '0', STR_PAD_LEFT);
        } while (\App\Models\User::where('referral_code', $code)->exists());

        return $code;
    }
};
