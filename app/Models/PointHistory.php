<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PointTransactionTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'point_histories', key: 'id')]
#[Fillable([
    'customer_id',
    'laundry_transaction_id',
    'user_id',
    'type',
    'points',
    'balance_before',
    'balance_after',
    'reason',
    'created_at',
])]
class PointHistory extends Model
{
    public $timestamps = false;

    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PointTransactionTypeEnum::class,
            'points' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke pelanggan pemilik poin.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke transaksi laundry pemicu poin (jika ada).
     *
     * @return BelongsTo<LaundryTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LaundryTransaction::class, 'laundry_transaction_id');
    }

    /**
     * Relasi ke akun kasir/admin yang mencatat mutasi poin.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
