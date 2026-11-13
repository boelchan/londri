<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel jenis layanan laundry beserta tarif aktifnya.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('name', 120);
            $table->string('unit', 20)->default('kg');
            $table->unsignedBigInteger('price');
            $table->string('estimated_duration', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_category_id', 'name']);
        });
    }

    /**
     * Menghapus tabel jenis layanan.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
