<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel transaksi utama laundry.
     */
    public function up(): void
    {
        Schema::create('laundry_transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('invoice_number', 40)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->string('discount_type', 20)->default('none');
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('extra_water_fee')->default(0);
            $table->unsignedBigInteger('assistant_fee')->default(0);
            $table->unsignedBigInteger('grand_total');
            $table->unsignedInteger('points_earned')->default(0);
            $table->string('payment_status', 20)->default('paid');
            $table->string('payment_method', 20)->default('cash');
            $table->string('laundry_status', 20)->default('received');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('laundry_status');
            $table->index('payment_status');
        });
    }

    /**
     * Menghapus tabel transaksi laundry.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transactions');
    }
};
