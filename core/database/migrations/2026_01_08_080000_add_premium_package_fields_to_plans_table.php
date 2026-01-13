<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'package_number')) {
                $table->unsignedTinyInteger('package_number')->default(0)->after('name');
            }
            if (!Schema::hasColumn('plans', 'is_premium_package')) {
                $table->boolean('is_premium_package')->default(false)->after('package_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'is_premium_package')) {
                $table->dropColumn('is_premium_package');
            }
            if (Schema::hasColumn('plans', 'package_number')) {
                $table->dropColumn('package_number');
            }
        });
    }
};
