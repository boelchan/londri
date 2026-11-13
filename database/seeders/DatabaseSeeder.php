<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ServiceUnitEnum;
use App\Enums\UserStatusEnum;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data dasar aplikasi LondriKita: peran, izin, akun Pemilik & Kasir, master kategori/layanan, dan pelanggan awal.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'pos-access',
            'transaction-manage',
            'customer-manage',
            'catalog-manage',
            'price-history-view',
            'point-adjust',
            'user-list',
            'user-tambah',
            'user-edit',
            'user-hapus',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashierRole->syncPermissions([
            'pos-access',
            'transaction-manage',
            'customer-manage',
        ]);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@londri.id'],
            [
                'name' => 'Pemilik Laundry',
                'password' => bcrypt('123'),
                'phone' => '081234567890',
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
        $adminUser->syncRoles(['administrator']);

        $cashierUser = User::updateOrCreate(
            ['email' => 'kasir@londri.id'],
            [
                'name' => 'Kasir Utama',
                'password' => bcrypt('123'),
                'phone' => '081298765432',
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
        $cashierUser->syncRoles(['cashier']);

        $this->seedCatalog();
        $this->seedCustomers();
    }

    /**
     * Mengisi master kategori dan jenis layanan laundry standar.
     */
    private function seedCatalog(): void
    {
        $catalog = [
            [
                'name' => 'Kiloan',
                'description' => 'Layanan cuci pakaian harian berdasarkan berat timbangan kilogram.',
                'services' => [
                    ['name' => 'Cuci Setrika Reguler', 'unit' => ServiceUnitEnum::KG->value, 'price' => 7000, 'estimated_duration' => '2 Hari'],
                    ['name' => 'Cuci Kering Lipat', 'unit' => ServiceUnitEnum::KG->value, 'price' => 5000, 'estimated_duration' => '2 Hari'],
                    ['name' => 'Setrika Saja', 'unit' => ServiceUnitEnum::KG->value, 'price' => 4500, 'estimated_duration' => '1 Hari'],
                    ['name' => 'Cuci Express 6 Jam', 'unit' => ServiceUnitEnum::KG->value, 'price' => 12000, 'estimated_duration' => '6 Jam'],
                ],
            ],
            [
                'name' => 'Satuan',
                'description' => 'Perawatan khusus per potong untuk item berukuran besar atau sensitif.',
                'services' => [
                    ['name' => 'Bed Cover Besar', 'unit' => ServiceUnitEnum::PCS->value, 'price' => 35000, 'estimated_duration' => '3 Hari'],
                    ['name' => 'Bed Cover Kecil', 'unit' => ServiceUnitEnum::PCS->value, 'price' => 25000, 'estimated_duration' => '3 Hari'],
                    ['name' => 'Jas / Blazer', 'unit' => ServiceUnitEnum::PCS->value, 'price' => 22000, 'estimated_duration' => '2 Hari'],
                    ['name' => 'Selimut Tebal', 'unit' => ServiceUnitEnum::PCS->value, 'price' => 20000, 'estimated_duration' => '2 Hari'],
                ],
            ],
            [
                'name' => 'Karpet & Rumah Tangga',
                'description' => 'Pencucian karpet, gorden, dan perlengkapan rumah tangga lainnya.',
                'services' => [
                    ['name' => 'Cuci Karpet Standar', 'unit' => ServiceUnitEnum::METER->value, 'price' => 18000, 'estimated_duration' => '4 Hari'],
                    ['name' => 'Cuci Gorden', 'unit' => ServiceUnitEnum::KG->value, 'price' => 15000, 'estimated_duration' => '3 Hari'],
                ],
            ],
            [
                'name' => 'Sepatu',
                'description' => 'Pembersihan mendalam untuk berbagai jenis sepatu.',
                'services' => [
                    ['name' => 'Deep Clean Sepatu', 'unit' => ServiceUnitEnum::PAIR->value, 'price' => 30000, 'estimated_duration' => '3 Hari'],
                ],
            ],
        ];

        foreach ($catalog as $catData) {
            $category = ServiceCategory::updateOrCreate(
                ['name' => $catData['name']],
                [
                    'description' => $catData['description'],
                    'is_active' => true,
                ]
            );

            foreach ($catData['services'] as $serviceData) {
                Service::firstOrCreate(
                    [
                        'service_category_id' => $category->id,
                        'name' => $serviceData['name'],
                    ],
                    [
                        'unit' => $serviceData['unit'],
                        'price' => $serviceData['price'],
                        'estimated_duration' => $serviceData['estimated_duration'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Mengisi beberapa data pelanggan awal untuk memudahkan pengujian operasional kasir.
     */
    private function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Ibu Sari Wijaya', 'phone' => '081311223344', 'address' => 'Jl. Melati No. 14', 'points' => 12],
            ['name' => 'Bapak Andi Pratama', 'phone' => '081355667788', 'address' => 'Perum Griya Asri Blok B3', 'points' => 5],
            ['name' => 'Nadia Kusuma', 'phone' => '085299887766', 'address' => 'Jl. Kenanga No. 8', 'points' => 0],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }
    }
}
