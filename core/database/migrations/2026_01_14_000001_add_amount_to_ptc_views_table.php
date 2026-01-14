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
        Schema::table('ptc_views', function (Blueprint $table) {
            if (!Schema::hasColumn('ptc_views', 'amount')) {
                $table->decimal('amount', 28, 8)->default(0)->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ptc_views', function (Blueprint $table) {
            if (Schema::hasColumn('ptc_views', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
