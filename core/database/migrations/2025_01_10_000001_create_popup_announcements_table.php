<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main popup announcements table
        Schema::create('popup_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->enum('target_type', ['all', 'specific'])->default('all'); // all = all users, specific = selected users
            $table->boolean('show_to_guests')->default(false);
            $table->boolean('show_once')->default(true); // Show only once per user
            $table->integer('priority')->default(0); // Higher = shown first
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Pivot table for targeting specific users
        Schema::create('popup_announcement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_announcement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['popup_announcement_id', 'user_id'], 'popup_user_unique');
        });

        // Tracking table for popup views
        Schema::create('popup_announcement_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_announcement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['popup_announcement_id', 'user_id'], 'popup_view_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_announcement_views');
        Schema::dropIfExists('popup_announcement_user');
        Schema::dropIfExists('popup_announcements');
    }
};
