<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'price_histories', key: 'id')]
#[Fillable([
    'service_id',
    'user_id',
    'category_name',
    'service_name',
    'old_price',
    'new_price',
    'notes',
    'created_at',
])]
class PriceHistory extends Model
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
            'old_price' => 'integer',
            'new_price' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke layanan yang diubah harganya.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Relasi ke akun admin yang melakukan perubahan harga.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
