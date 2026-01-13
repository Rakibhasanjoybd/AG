<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hold_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('commission_type');
            $table->string('source_description')->nullable();
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->decimal('instant_amount', 28, 8)->default(0);
            $table->decimal('hold_amount', 28, 8)->default(0);
            $table->date('available_date');
            $table->tinyInteger('is_transferred')->default(0);
            $table->timestamp('transferred_at')->nullable();
            $table->string('trx')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hold_wallet_transactions');
    }
};
