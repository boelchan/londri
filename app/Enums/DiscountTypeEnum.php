<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscountTypeEnum: string
{
    case NONE = 'none';
    case NOMINAL = 'nominal';
    case PERCENT = 'percent';

    /**
     * Mengambil label tipe diskon dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Tanpa Diskon',
            self::NOMINAL => 'Nominal (Rp)',
            self::PERCENT => 'Persentase (%)',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh tipe diskon.
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
