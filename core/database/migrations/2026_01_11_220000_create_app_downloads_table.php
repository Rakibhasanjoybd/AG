<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_downloads', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['android', 'ios'])->unique();
            $table->string('version', 50)->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('package_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('force_update')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_downloads');
    }
};
