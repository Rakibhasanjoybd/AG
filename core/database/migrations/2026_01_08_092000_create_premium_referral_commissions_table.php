<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('premium_referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedTinyInteger('package_number')->default(0);
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('source', 50)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
            $table->index(['referred_user_id', 'status']);
            $table->index(['status', 'created_at']);

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('premium_referral_commissions');
    }
};
