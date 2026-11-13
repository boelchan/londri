<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscountTypeEnum;
use App\Enums\LaundryStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'laundry_transactions', key: 'id')]
#[Fillable([
    'invoice_number',
    'customer_id',
    'user_id',
    'subtotal',
    'discount_type',
    'discount_value',
    'discount_amount',
    'extra_water_fee',
    'assistant_fee',
    'grand_total',
    'points_earned',
    'payment_status',
    'payment_method',
    'laundry_status',
    'notes',
])]
class LaundryTransaction extends Model
{
    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_type' => DiscountTypeEnum::class,
            'discount_value' => 'integer',
            'discount_amount' => 'integer',
            'extra_water_fee' => 'integer',
            'assistant_fee' => 'integer',
            'grand_total' => 'integer',
            'points_earned' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'payment_method' => PaymentMethodEnum::class,
            'laundry_status' => LaundryStatusEnum::class,
        ];
    }

    /**
     * Relasi ke pelanggan pemilik transaksi.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke kasir/admin yang memproses transaksi.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke detail item layanan dalam transaksi ini.
     *
     * @return HasMany<LaundryTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(LaundryTransactionItem::class, 'laundry_transaction_id');
    }
}
