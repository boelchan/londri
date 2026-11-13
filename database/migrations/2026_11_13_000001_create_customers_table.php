<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel pelanggan laundry dan saldo poin loyalitas.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->unique();
            $table->text('address')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel pelanggan.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
