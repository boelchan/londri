<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceUnitEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'services', key: 'id')]
#[Fillable([
    'service_category_id',
    'name',
    'unit',
    'price',
    'estimated_duration',
    'is_active',
])]
class Service extends Model
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
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke kategori induk layanan.
     *
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Relasi ke log riwayat perubahan harga layanan.
     *
     * @return HasMany<PriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'service_id');
    }

    /**
     * Relasi ke item transaksi yang menggunakan layanan ini.
     *
     * @return HasMany<LaundryTransactionItem, $this>
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(LaundryTransactionItem::class, 'service_id');
    }
}
