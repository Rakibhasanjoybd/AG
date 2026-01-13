<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('commission_logs')) {
            return;
        }

        Schema::table('commission_logs', function (Blueprint $table) {
            // Nullable for backwards compatibility with existing rows
            if (!Schema::hasColumn('commission_logs', 'source_type')) {
                $table->string('source_type', 64)->nullable()->after('type');
            }
            if (!Schema::hasColumn('commission_logs', 'source_id')) {
                // Using string keeps it flexible (trx string, numeric ids, etc.)
                $table->string('source_id', 64)->nullable()->after('source_type');
            }
        });

        // Unique (to_id, source_type, source_id) => fire-once per credited user per source
        if (!$this->hasIndex('commission_logs', 'commission_logs_to_source_unique')) {
            Schema::table('commission_logs', function (Blueprint $table) {
                $table->unique(['to_id', 'source_type', 'source_id'], 'commission_logs_to_source_unique');
            });
        }

        // Helpful for lookups
        if (!$this->hasIndex('commission_logs', 'commission_logs_source_lookup_index')) {
            Schema::table('commission_logs', function (Blueprint $table) {
                $table->index(['source_type', 'source_id'], 'commission_logs_source_lookup_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('commission_logs')) {
            return;
        }

        Schema::table('commission_logs', function (Blueprint $table) {
            if ($this->hasIndex('commission_logs', 'commission_logs_to_source_unique')) {
                $table->dropUnique('commission_logs_to_source_unique');
            }
            if ($this->hasIndex('commission_logs', 'commission_logs_source_lookup_index')) {
                $table->dropIndex('commission_logs_source_lookup_index');
            }

            if (Schema::hasColumn('commission_logs', 'source_id')) {
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('commission_logs', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
