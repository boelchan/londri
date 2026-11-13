<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel riwayat perubahan harga (audit trail) layanan laundry.
     */
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('category_name', 100);
            $table->string('service_name', 120);
            $table->unsignedBigInteger('old_price');
            $table->unsignedBigInteger('new_price');
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    /**
     * Menghapus tabel riwayat perubahan harga.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
