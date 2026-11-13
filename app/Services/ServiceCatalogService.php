<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServiceCatalogService
{
    /**
     * Memperbarui data layanan dan otomatis mencatat audit trail apabila tarif harga berubah.
     *
     * @param  array{
     *     service_category_id: int,
     *     name: string,
     *     unit: string,
     *     price: int,
     *     estimated_duration?: ?string,
     *     is_active: bool
     * }  $data
     */
    public function updateService(Service $service, array $data, User $admin, ?string $priceChangeNotes = null): Service
    {
        return DB::transaction(function () use ($service, $data, $admin, $priceChangeNotes): Service {
            $lockedService = Service::with('category')->lockForUpdate()->findOrFail($service->id);
            $oldPrice = (int) $lockedService->price;
            $newPrice = (int) $data['price'];

            $lockedService->update($data);
            $lockedService->load('category');

            if ($oldPrice !== $newPrice) {
                $categoryName = $lockedService->category?->name ?? '-';
                if ((int) $data['service_category_id'] !== (int) $service->service_category_id) {
                    $categoryName = ServiceCategory::query()->whereKey($data['service_category_id'])->value('name') ?? $categoryName;
                }

                PriceHistory::create([
                    'service_id' => $lockedService->id,
                    'user_id' => $admin->id,
                    'category_name' => $categoryName,
                    'service_name' => $lockedService->name,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'notes' => $priceChangeNotes ? trim($priceChangeNotes) : null,
                    'created_at' => now(),
                ]);
            }

            return $lockedService;
        });
    }
}
