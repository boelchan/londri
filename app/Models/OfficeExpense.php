<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExpenseCategoryEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'office_expenses', key: 'id')]
#[Fillable([
    'user_id',
    'expense_date',
    'category',
    'amount',
    'description',
    'notes',
])]
class OfficeExpense extends Model
{
    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'category' => ExpenseCategoryEnum::class,
            'amount' => 'integer',
        ];
    }

    /**
     * Relasi ke akun pemilik/admin yang mencatat pengeluaran.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
