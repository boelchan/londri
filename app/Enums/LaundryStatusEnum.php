<?php

declare(strict_types=1);

namespace App\Enums;

enum LaundryStatusEnum: string
{
    case RECEIVED = 'received';
    case WASHING = 'washing';
    case IRONING = 'ironing';
    case READY = 'ready';
    case COMPLETED = 'completed';

    /**
     * Mengambil label status cucian dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Diterima',
            self::WASHING => 'Dicuci',
            self::IRONING => 'Disetrika',
            self::READY => 'Siap Diambil',
            self::COMPLETED => 'Selesai',
        };
    }

    /**
     * Mengambil warna badge TallStackUI untuk status cucian.
     */
    public function color(): string
    {
        return match ($this) {
            self::RECEIVED => 'slate',
            self::WASHING => 'sky',
            self::IRONING => 'amber',
            self::READY => 'violet',
            self::COMPLETED => 'emerald',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh status cucian.
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
