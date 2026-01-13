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
        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Contact name (e.g., "Customer Support")
            $table->string('department', 100); // Department (e.g., "Technical Support", "Sales")
            $table->string('phone_number', 20); // WhatsApp number with country code
            $table->string('profile_image')->nullable(); // Profile image path
            $table->text('message_format')->nullable(); // Pre-filled message format
            $table->text('description')->nullable(); // Short description of what this contact helps with
            $table->integer('display_order')->default(0); // Order in which to display
            $table->boolean('is_active')->default(true); // Enable/disable contact
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_contacts');
    }
};
