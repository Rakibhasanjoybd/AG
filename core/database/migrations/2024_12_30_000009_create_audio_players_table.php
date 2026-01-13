<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audio_players', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('audio_file');
            $table->string('thumbnail')->nullable();
            $table->tinyInteger('autoplay')->default(0);
            $table->tinyInteger('loop')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audio_players');
    }
};
