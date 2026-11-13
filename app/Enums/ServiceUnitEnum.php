<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceUnitEnum: string
{
    case KG = 'kg';
    case PCS = 'pcs';
    case METER = 'm2';
    case PAIR = 'pasang';

    /**
     * Mengambil label satuan layanan dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::KG => 'Kilogram (kg)',
            self::PCS => 'Satuan (pcs)',
            self::METER => 'Meter Persegi (m²)',
            self::PAIR => 'Pasang',
        };
    }

    /**
     * Mengambil singkatan satuan untuk tampilan ringkas pada kasir dan nota.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::KG => 'kg',
            self::PCS => 'pcs',
            self::METER => 'm²',
            self::PAIR => 'pasang',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh satuan layanan.
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
