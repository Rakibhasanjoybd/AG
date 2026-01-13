<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptc_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ptc_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('rating')->comment('1-5 stars');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('ptc_id')->references('id')->on('ptcs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['ptc_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptc_reviews');
    }
};
