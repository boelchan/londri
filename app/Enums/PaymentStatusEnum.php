<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';

    /**
     * Mengambil label status pembayaran dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Lunas',
            self::UNPAID => 'Belum Bayar',
        };
    }

    /**
     * Mengambil warna badge TallStackUI untuk status pembayaran.
     */
    public function color(): string
    {
        return match ($this) {
            self::PAID => 'emerald',
            self::UNPAID => 'amber',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh status pembayaran.
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
