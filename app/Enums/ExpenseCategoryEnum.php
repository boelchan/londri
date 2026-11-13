<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpenseCategoryEnum: string
{
    case DETERGENT_CHEMICAL = 'detergent_chemical';
    case UTILITIES = 'utilities';
    case SALARY_BONUS = 'salary_bonus';
    case EQUIPMENT_MAINTENANCE = 'equipment_maintenance';
    case PACKAGING = 'packaging';
    case RENT = 'rent';
    case OTHER = 'other';

    /**
     * Mengambil label kategori pengeluaran kantor dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::DETERGENT_CHEMICAL => 'Deterjen, Pewangi & Kimia',
            self::UTILITIES => 'Listrik, Air & Internet',
            self::SALARY_BONUS => 'Gaji & Bonus Karyawan',
            self::EQUIPMENT_MAINTENANCE => 'Perawatan & Servis Mesin',
            self::PACKAGING => 'Plastik, Gantungan & Kemasan',
            self::RENT => 'Sewa Tempat',
            self::OTHER => 'Operasional Lainnya',
        };
    }

    /**
     * Mengambil daftar nilai string dari seluruh kategori pengeluaran.
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
