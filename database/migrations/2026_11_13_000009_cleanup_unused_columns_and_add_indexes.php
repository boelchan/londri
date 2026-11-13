<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghapus kolom dan tabel sisa template yang tidak digunakan serta menambah index komposit untuk performa rekap.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('users', 'jabatan') ? 'jabatan' : null,
                Schema::hasColumn('users', 'email_verified_at') ? 'email_verified_at' : null,
                Schema::hasColumn('users', 'two_factor_secret') ? 'two_factor_secret' : null,
                Schema::hasColumn('users', 'two_factor_recovery_codes') ? 'two_factor_recovery_codes' : null,
                Schema::hasColumn('users', 'two_factor_confirmed_at') ? 'two_factor_confirmed_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('password_reset_tokens');

        Schema::table('laundry_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'laundry_tx_user_created_idx');
        });
    }

    /**
     * Mengembalikan kolom dan tabel yang dihapus jika migrasi dibatalkan.
     */
    public function down(): void
    {
        Schema::table('laundry_transactions', function (Blueprint $table) {
            $table->dropIndex('laundry_tx_user_created_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 80)->nullable()->after('phone');
            $table->timestamp('email_verified_at')->nullable()->after('status');
            $table->text('two_factor_secret')->nullable()->after('phone');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};
