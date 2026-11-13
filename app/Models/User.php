<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Table(name: 'users', key: 'id')]
#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'status',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Konfigurasi konversi tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke seluruh transaksi laundry yang diproses oleh pengguna.
     *
     * @return HasMany<LaundryTransaction, $this>
     */
    public function laundryTransactions(): HasMany
    {
        return $this->hasMany(LaundryTransaction::class, 'user_id');
    }

    /**
     * Relasi ke riwayat perubahan harga yang dilakukan oleh admin.
     *
     * @return HasMany<PriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'user_id');
    }

    /**
     * Relasi ke riwayat mutasi poin yang dicatat oleh pengguna.
     *
     * @return HasMany<PointHistory, $this>
     */
    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class, 'user_id');
    }

    /**
     * Memutus seluruh sesi login aktif dan token ingat saya milik pengguna ini.
     */
    public function invalidateSessions(): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $this->getKey())
                ->delete();
        }

        $this->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
    }

    /**
     * Memeriksa apakah pengguna memiliki peran Pemilik (Admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrator');
    }

    /**
     * Memeriksa apakah pengguna memiliki peran Kasir.
     */
    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }
}
