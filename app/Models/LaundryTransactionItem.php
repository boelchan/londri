<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceUnitEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'laundry_transaction_items', key: 'id')]
#[Fillable([
    'laundry_transaction_id',
    'service_id',
    'category_name',
    'service_name',
    'unit',
    'unit_price',
    'quantity',
    'subtotal',
])]
class LaundryTransactionItem extends Model
{
    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit' => ServiceUnitEnum::class,
            'unit_price' => 'integer',
            'quantity' => 'float',
            'subtotal' => 'integer',
        ];
    }

    /**
     * Relasi ke transaksi induk.
     *
     * @return BelongsTo<LaundryTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LaundryTransaction::class, 'laundry_transaction_id');
    }

    /**
     * Relasi ke master layanan terkait.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
