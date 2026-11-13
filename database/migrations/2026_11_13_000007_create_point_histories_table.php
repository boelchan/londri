<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel riwayat mutasi poin loyalitas pelanggan.
     */
    public function up(): void
    {
        Schema::create('point_histories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('laundry_transaction_id')->nullable()->constrained('laundry_transactions')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 30);
            $table->integer('points');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    /**
     * Menghapus tabel riwayat mutasi poin.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_histories');
    }
};
