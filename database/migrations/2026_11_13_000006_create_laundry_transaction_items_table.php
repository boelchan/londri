<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel detail item layanan pada setiap transaksi laundry.
     */
    public function up(): void
    {
        Schema::create('laundry_transaction_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('laundry_transaction_id')->constrained('laundry_transactions')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->string('category_name', 100);
            $table->string('service_name', 120);
            $table->string('unit', 20);
            $table->unsignedBigInteger('unit_price');
            $table->decimal('quantity', 8, 2);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel detail item transaksi laundry.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transaction_items');
    }
};
