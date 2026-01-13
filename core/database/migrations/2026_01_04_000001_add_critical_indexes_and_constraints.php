<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * CRITICAL SECURITY MIGRATION
 * 
 * This migration adds:
 * 1. Foreign key constraints to ensure data integrity
 * 2. Performance indexes for commonly queried columns
 * 3. Unique constraints to prevent duplicate entries
 * 
 * RUN THIS AFTER BACKING UP YOUR DATABASE!
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================
        // STEP 1: CLEAN UP ORPHANED RECORDS
        // =========================================
        
        $this->cleanOrphanedRecords();
        
        // =========================================
        // STEP 2: ADD PERFORMANCE INDEXES
        // =========================================
        
        $this->addPerformanceIndexes();
        
        // =========================================
        // STEP 3: ADD FOREIGN KEY CONSTRAINTS
        // =========================================
        
        $this->addForeignKeys();
        
        // =========================================
        // STEP 4: ADD UNIQUE CONSTRAINTS
        // =========================================
        
        $this->addUniqueConstraints();
    }

    /**
     * Clean up orphaned records before adding foreign keys
     */
    private function cleanOrphanedRecords(): void
    {
        // Log counts before cleanup
        $counts = [
            'transactions' => DB::table('transactions')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->count(),
            'deposits' => DB::table('deposits')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->count(),
            'withdrawals' => DB::table('withdrawals')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->count(),
            'commission_logs' => DB::table('commission_logs')
                ->where(function ($query) {
                    $query->whereNotIn('to_id', DB::table('users')->select('id'))
                          ->orWhereNotIn('from_id', DB::table('users')->select('id'));
                })
                ->count(),
        ];
        
        \Log::info('Orphaned records found:', $counts);
        
        // Delete orphaned records
        DB::table('transactions')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->delete();
            
        DB::table('deposits')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->delete();
            
        DB::table('withdrawals')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->delete();
            
        DB::table('commission_logs')
            ->whereNotIn('to_id', DB::table('users')->select('id'))
            ->delete();
            
        DB::table('commission_logs')
            ->whereNotIn('from_id', DB::table('users')->select('id'))
            ->delete();
            
        // Clean up ptc_views
        if (Schema::hasTable('ptc_views')) {
            DB::table('ptc_views')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->delete();
        }
        
        // Clean up user_logins
        if (Schema::hasTable('user_logins')) {
            DB::table('user_logins')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->delete();
        }
        
        // Clean up hold_wallet_transactions
        if (Schema::hasTable('hold_wallet_transactions')) {
            DB::table('hold_wallet_transactions')
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->delete();
        }
        
        // Fix users with invalid plan_id
        DB::table('users')
            ->whereNotNull('plan_id')
            ->whereNotIn('plan_id', DB::table('plans')->select('id'))
            ->update(['plan_id' => null]);
            
        // Fix users with invalid ref_by
        DB::table('users')
            ->whereNotNull('ref_by')
            ->whereNotIn('ref_by', DB::table('users')->select('id'))
            ->update(['ref_by' => null]);
    }

    /**
     * Add performance indexes
     */
    private function addPerformanceIndexes(): void
    {
        // Transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->hasIndex('transactions', 'transactions_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->hasIndex('transactions', 'transactions_trx_index')) {
                $table->index('trx');
            }
            if (!$this->hasIndex('transactions', 'transactions_remark_index')) {
                $table->index('remark');
            }
            if (!$this->hasIndex('transactions', 'transactions_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
        });
        
        // Deposits table
        Schema::table('deposits', function (Blueprint $table) {
            if (!$this->hasIndex('deposits', 'deposits_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!$this->hasIndex('deposits', 'deposits_trx_index')) {
                $table->index('trx');
            }
            if (!$this->hasIndex('deposits', 'deposits_status_index')) {
                $table->index('status');
            }
        });
        
        // Withdrawals table
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!$this->hasIndex('withdrawals', 'withdrawals_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!$this->hasIndex('withdrawals', 'withdrawals_trx_index')) {
                $table->index('trx');
            }
            if (!$this->hasIndex('withdrawals', 'withdrawals_status_index')) {
                $table->index('status');
            }
        });
        
        // Commission logs table
        Schema::table('commission_logs', function (Blueprint $table) {
            if (!$this->hasIndex('commission_logs', 'commission_logs_to_id_index')) {
                $table->index('to_id');
            }
            if (!$this->hasIndex('commission_logs', 'commission_logs_from_id_index')) {
                $table->index('from_id');
            }
            if (!$this->hasIndex('commission_logs', 'commission_logs_to_id_type_index')) {
                $table->index(['to_id', 'type']);
            }
        });
        
        // PTC views table
        if (Schema::hasTable('ptc_views')) {
            Schema::table('ptc_views', function (Blueprint $table) {
                if (!$this->hasIndex('ptc_views', 'ptc_views_user_id_view_date_index')) {
                    $table->index(['user_id', 'view_date']);
                }
                if (!$this->hasIndex('ptc_views', 'ptc_views_ptc_id_index')) {
                    $table->index('ptc_id');
                }
            });
        }
        
        // User logins table
        if (Schema::hasTable('user_logins')) {
            Schema::table('user_logins', function (Blueprint $table) {
                if (!$this->hasIndex('user_logins', 'user_logins_user_id_index')) {
                    $table->index('user_id');
                }
                if (!$this->hasIndex('user_logins', 'user_logins_user_id_created_at_index')) {
                    $table->index(['user_id', 'created_at']);
                }
            });
        }
        
        // Hold wallet transactions table
        if (Schema::hasTable('hold_wallet_transactions')) {
            Schema::table('hold_wallet_transactions', function (Blueprint $table) {
                if (!$this->hasIndex('hold_wallet_transactions', 'hold_wallet_user_transferred_date_index')) {
                    $table->index(['user_id', 'is_transferred', 'available_date'], 'hold_wallet_user_transferred_date_index');
                }
            });
        }
        
        // Users table - ensure indexes exist
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }
            if (!$this->hasIndex('users', 'users_username_index')) {
                $table->index('username');
            }
            if (!$this->hasIndex('users', 'users_ref_by_index')) {
                $table->index('ref_by');
            }
            if (!$this->hasIndex('users', 'users_referral_code_index')) {
                $table->index('referral_code');
            }
        });
        
        // VIP task completions table
        if (Schema::hasTable('vip_task_completions')) {
            Schema::table('vip_task_completions', function (Blueprint $table) {
                if (!$this->hasIndex('vip_task_completions', 'vip_task_completions_user_task_date_index')) {
                    $table->index(['user_id', 'vip_task_id', 'completion_date'], 'vip_task_completions_user_task_date_index');
                }
            });
        }
    }

    /**
     * Add foreign key constraints
     */
    private function addForeignKeys(): void
    {
        // Transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->hasForeignKey('transactions', 'transactions_user_id_foreign')) {
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
        });
        
        // Deposits table
        Schema::table('deposits', function (Blueprint $table) {
            if (!$this->hasForeignKey('deposits', 'deposits_user_id_foreign')) {
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
        });
        
        // Withdrawals table
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!$this->hasForeignKey('withdrawals', 'withdrawals_user_id_foreign')) {
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
            if (!$this->hasForeignKey('withdrawals', 'withdrawals_method_id_foreign')) {
                $table->foreign('method_id')
                    ->references('id')->on('withdraw_methods')
                    ->onDelete('restrict');
            }
        });
        
        // Commission logs table
        Schema::table('commission_logs', function (Blueprint $table) {
            if (!$this->hasForeignKey('commission_logs', 'commission_logs_to_id_foreign')) {
                $table->foreign('to_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
            if (!$this->hasForeignKey('commission_logs', 'commission_logs_from_id_foreign')) {
                $table->foreign('from_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
        });
        
        // Users table
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasForeignKey('users', 'users_plan_id_foreign')) {
                $table->foreign('plan_id')
                    ->references('id')->on('plans')
                    ->onDelete('set null');
            }
            if (!$this->hasForeignKey('users', 'users_ref_by_foreign')) {
                $table->foreign('ref_by')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            }
        });
        
        // Hold wallet transactions
        if (Schema::hasTable('hold_wallet_transactions')) {
            Schema::table('hold_wallet_transactions', function (Blueprint $table) {
                if (!$this->hasForeignKey('hold_wallet_transactions', 'hold_wallet_transactions_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
                if (Schema::hasColumn('hold_wallet_transactions', 'from_user_id')) {
                    if (!$this->hasForeignKey('hold_wallet_transactions', 'hold_wallet_transactions_from_user_id_foreign')) {
                        $table->foreign('from_user_id')
                            ->references('id')->on('users')
                            ->onDelete('set null');
                    }
                }
            });
        }
        
        // PTC views
        if (Schema::hasTable('ptc_views')) {
            Schema::table('ptc_views', function (Blueprint $table) {
                if (!$this->hasForeignKey('ptc_views', 'ptc_views_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
                if (!$this->hasForeignKey('ptc_views', 'ptc_views_ptc_id_foreign')) {
                    $table->foreign('ptc_id')
                        ->references('id')->on('ptcs')
                        ->onDelete('cascade');
                }
            });
        }
        
        // User logins
        if (Schema::hasTable('user_logins')) {
            Schema::table('user_logins', function (Blueprint $table) {
                if (!$this->hasForeignKey('user_logins', 'user_logins_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
            });
        }
        
        // User notifications
        if (Schema::hasTable('user_notifications')) {
            Schema::table('user_notifications', function (Blueprint $table) {
                if (!$this->hasForeignKey('user_notifications', 'user_notifications_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
            });
        }
        
        // Admin notifications
        if (Schema::hasTable('admin_notifications')) {
            Schema::table('admin_notifications', function (Blueprint $table) {
                if (!$this->hasForeignKey('admin_notifications', 'admin_notifications_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
            });
        }
        
        // VIP task completions
        if (Schema::hasTable('vip_task_completions')) {
            Schema::table('vip_task_completions', function (Blueprint $table) {
                if (!$this->hasForeignKey('vip_task_completions', 'vip_task_completions_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
                if (!$this->hasForeignKey('vip_task_completions', 'vip_task_completions_vip_task_id_foreign')) {
                    $table->foreign('vip_task_id')
                        ->references('id')->on('vip_tasks')
                        ->onDelete('cascade');
                }
            });
        }
        
        // Support tickets
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (!$this->hasForeignKey('support_tickets', 'support_tickets_user_id_foreign')) {
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Add unique constraints for data integrity
     */
    private function addUniqueConstraints(): void
    {
        // Unique transaction reference
        Schema::table('transactions', function (Blueprint $table) {
            // Note: trx is NOT unique as multiple transactions can share same trx (e.g., transfer)
            // Just ensure index exists (already done above)
        });
        
        // Unique deposit transaction reference
        Schema::table('deposits', function (Blueprint $table) {
            // trx should be unique for deposits
            if (!$this->hasIndex('deposits', 'deposits_trx_unique')) {
                // First, remove duplicates if any
                $duplicates = DB::table('deposits')
                    ->select('trx')
                    ->groupBy('trx')
                    ->havingRaw('COUNT(*) > 1')
                    ->pluck('trx');
                    
                foreach ($duplicates as $trx) {
                    $deposits = DB::table('deposits')->where('trx', $trx)->get();
                    foreach ($deposits->skip(1) as $deposit) {
                        DB::table('deposits')
                            ->where('id', $deposit->id)
                            ->update(['trx' => $trx . '_' . $deposit->id]);
                    }
                }
                
                $table->unique('trx');
            }
        });
        
        // Unique users constraints
        Schema::table('users', function (Blueprint $table) {
            // email and username should already be unique, but verify
            if (!$this->hasIndex('users', 'users_email_unique')) {
                $table->unique('email');
            }
            if (!$this->hasIndex('users', 'users_username_unique')) {
                $table->unique('username');
            }
            if (!$this->hasIndex('users', 'users_referral_code_unique')) {
                // First fix any duplicate referral codes
                $duplicates = DB::table('users')
                    ->select('referral_code')
                    ->whereNotNull('referral_code')
                    ->groupBy('referral_code')
                    ->havingRaw('COUNT(*) > 1')
                    ->pluck('referral_code');
                    
                foreach ($duplicates as $code) {
                    $users = DB::table('users')->where('referral_code', $code)->get();
                    foreach ($users->skip(1) as $user) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['referral_code' => $code . '_' . $user->id]);
                    }
                }
                
                $table->unique('referral_code');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Check if a foreign key exists on a table
     */
    private function hasForeignKey(string $table, string $keyName): bool
    {
        $database = config('database.connections.mysql.database');
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table, $keyName]);
        
        return count($foreignKeys) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys first
        $tables = [
            'transactions' => ['transactions_user_id_foreign'],
            'deposits' => ['deposits_user_id_foreign'],
            'withdrawals' => ['withdrawals_user_id_foreign', 'withdrawals_method_id_foreign'],
            'commission_logs' => ['commission_logs_to_id_foreign', 'commission_logs_from_id_foreign'],
            'users' => ['users_plan_id_foreign', 'users_ref_by_foreign'],
            'hold_wallet_transactions' => ['hold_wallet_transactions_user_id_foreign', 'hold_wallet_transactions_from_user_id_foreign'],
            'ptc_views' => ['ptc_views_user_id_foreign', 'ptc_views_ptc_id_foreign'],
            'user_logins' => ['user_logins_user_id_foreign'],
            'user_notifications' => ['user_notifications_user_id_foreign'],
            'admin_notifications' => ['admin_notifications_user_id_foreign'],
            'vip_task_completions' => ['vip_task_completions_user_id_foreign', 'vip_task_completions_vip_task_id_foreign'],
            'support_tickets' => ['support_tickets_user_id_foreign'],
        ];
        
        foreach ($tables as $table => $foreignKeys) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table, $foreignKeys) {
                    foreach ($foreignKeys as $key) {
                        if ($this->hasForeignKey($table, $key)) {
                            $blueprint->dropForeign($key);
                        }
                    }
                });
            }
        }
        
        // Note: Indexes are generally safe to keep, so we don't remove them in rollback
    }
};
