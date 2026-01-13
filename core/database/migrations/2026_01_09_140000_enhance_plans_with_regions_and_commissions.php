<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('region', 50)->default('ASIA')->after('name'); // ASIA, EUROPE, AMERICA, etc.
            $table->string('display_code', 20)->nullable()->after('region'); // H1, H2, H3, etc.
            $table->string('icon', 100)->default('fa-gem')->after('display_code');
            $table->string('color_scheme', 50)->default('green')->after('icon'); // green, blue, orange, red

            // Commission structure - Level A
            $table->decimal('commission_level_a_rate', 8, 2)->default(12.00)->after('ref_level');
            $table->decimal('commission_level_a_max', 12, 2)->nullable()->after('commission_level_a_rate');

            // Commission structure - Level B
            $table->decimal('commission_level_b_rate', 8, 2)->default(4.00)->after('commission_level_a_max');
            $table->decimal('commission_level_b_max', 12, 2)->nullable()->after('commission_level_b_rate');

            // Commission structure - Level C
            $table->decimal('commission_level_c_rate', 8, 2)->default(1.00)->after('commission_level_b_max');
            $table->decimal('commission_level_c_max', 12, 2)->nullable()->after('commission_level_c_rate');

            // Task commission structure
            $table->decimal('task_commission_a_rate', 8, 2)->default(5.00)->after('commission_level_c_max');
            $table->decimal('task_commission_b_rate', 8, 2)->default(2.00)->after('task_commission_a_rate');
            $table->decimal('task_commission_c_rate', 8, 2)->default(1.00)->after('task_commission_b_rate');

            $table->integer('sort_order')->default(0)->after('status');
            $table->text('features')->nullable()->after('sort_order'); // JSON array of features
            $table->boolean('is_featured')->default(false)->after('features');
            $table->boolean('is_popular')->default(false)->after('is_featured');
        });
    }

    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'region', 'display_code', 'icon', 'color_scheme',
                'commission_level_a_rate', 'commission_level_a_max',
                'commission_level_b_rate', 'commission_level_b_max',
                'commission_level_c_rate', 'commission_level_c_max',
                'task_commission_a_rate', 'task_commission_b_rate', 'task_commission_c_rate',
                'sort_order', 'features', 'is_featured', 'is_popular'
            ]);
        });
    }
};
