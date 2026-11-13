<?php

declare(strict_types=1);

namespace App\Enums;

enum PointTransactionTypeEnum: string
{
    case EARNED = 'earned';
    case MANUAL_ADD = 'manual_add';
    case MANUAL_DEDUCT = 'manual_deduct';

    /**
     * Mengambil label tipe mutasi poin dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::EARNED => 'Poin Transaksi',
            self::MANUAL_ADD => 'Penambahan Manual',
            self::MANUAL_DEDUCT => 'Pengurangan / Penukaran',
        };
    }

    /**
     * Mengambil warna badge TallStackUI untuk tipe mutasi poin.
     */
    public function color(): string
    {
        return match ($this) {
            self::EARNED => 'emerald',
            self::MANUAL_ADD => 'sky',
            self::MANUAL_DEDUCT => 'rose',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh tipe mutasi poin.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Mengambil daftar pilihan untuk komponen select TallStackUI.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function choices(): array
    {
        return array_map(
            fn (self $case): array => [
                'label' => $case->label(),
                'value' => $case->value,
            ],
            self::cases()
        );
    }
}
