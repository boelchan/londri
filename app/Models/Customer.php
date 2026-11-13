<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'customers', key: 'id')]
#[Fillable([
    'name',
    'phone',
    'address',
    'points',
])]
class Customer extends Model
{
    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    /**
     * Relasi ke seluruh transaksi laundry milik pelanggan.
     *
     * @return HasMany<LaundryTransaction, $this>
     */
    public function laundryTransactions(): HasMany
    {
        return $this->hasMany(LaundryTransaction::class, 'customer_id');
    }

    /**
     * Relasi ke riwayat mutasi poin loyalitas pelanggan.
     *
     * @return HasMany<PointHistory, $this>
     */
    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class, 'customer_id');
    }
}
