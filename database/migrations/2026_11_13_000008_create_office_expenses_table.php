<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel pengeluaran operasional kantor laundry.
     */
    public function up(): void
    {
        Schema::create('office_expenses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('expense_date');
            $table->string('category', 50);
            $table->unsignedBigInteger('amount');
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('expense_date');
            $table->index('category');
        });
    }

    /**
     * Menghapus tabel pengeluaran kantor.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_expenses');
    }
};
