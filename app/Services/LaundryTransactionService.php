<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DiscountTypeEnum;
use App\Enums\LaundryStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PointTransactionTypeEnum;
use App\Models\Customer;
use App\Models\LaundryTransaction;
use App\Models\LaundryTransactionItem;
use App\Models\PointHistory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LaundryTransactionService
{
    public const int RUPIAH_PER_POINT = 10000;

    /**
     * Menghitung ringkasan subtotal, potongan diskon, grand total, dan estimasi poin loyalitas.
     *
     * @param  array<int, array{service_id: int, quantity: float|int|string}>  $items
     * @return array{
     *     subtotal: int,
     *     discount_amount: int,
     *     extra_water_fee: int,
     *     assistant_fee: int,
     *     grand_total: int,
     *     points_earned: int
     * }
     */
    public function calculateSummary(
        array $items,
        DiscountTypeEnum $discountType,
        int $discountValue,
        int $extraWaterFee,
        int $assistantFee
    ): array {
        $subtotal = 0;

        if ($items !== []) {
            $serviceIds = array_values(array_unique(array_map(fn (array $item): int => (int) $item['service_id'], $items)));
            $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $service = $services->get((int) $item['service_id']);
                $qty = max(0.0, (float) ($item['quantity'] ?? 0));
                if ($service && $qty > 0) {
                    $subtotal += (int) round($service->price * $qty);
                }
            }
        }

        $safeDiscountValue = max(0, $discountValue);
        $discountAmount = match ($discountType) {
            DiscountTypeEnum::NONE => 0,
            DiscountTypeEnum::NOMINAL => min($subtotal, $safeDiscountValue),
            DiscountTypeEnum::PERCENT => (int) round($subtotal * min(100, $safeDiscountValue) / 100),
        };

        $safeExtraWaterFee = max(0, $extraWaterFee);
        $safeAssistantFee = max(0, $assistantFee);

        $grandTotal = max(0, $subtotal - $discountAmount + $safeExtraWaterFee + $safeAssistantFee);
        $pointsEarned = intdiv($grandTotal, self::RUPIAH_PER_POINT);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'extra_water_fee' => $safeExtraWaterFee,
            'assistant_fee' => $safeAssistantFee,
            'grand_total' => $grandTotal,
            'points_earned' => $pointsEarned,
        ];
    }

    /**
     * Membuat transaksi laundry baru beserta item layanan dan penambahan poin loyalitas otomatis.
     *
     * @param  array<int, array{service_id: int, quantity: float|int|string}>  $items
     */
    public function createTransaction(
        Customer $customer,
        User $cashier,
        array $items,
        DiscountTypeEnum $discountType,
        int $discountValue,
        int $extraWaterFee,
        int $assistantFee,
        PaymentStatusEnum $paymentStatus,
        PaymentMethodEnum $paymentMethod,
        ?string $notes = null
    ): LaundryTransaction {
        if ($items === []) {
            throw new InvalidArgumentException('Minimal satu layanan harus dipilih.');
        }

        return DB::transaction(function () use (
            $customer,
            $cashier,
            $items,
            $discountType,
            $discountValue,
            $extraWaterFee,
            $assistantFee,
            $paymentStatus,
            $paymentMethod,
            $notes
        ): LaundryTransaction {
            $lockedCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);

            $serviceIds = array_values(array_unique(array_map(fn (array $item): int => (int) $item['service_id'], $items)));
            $services = Service::with('category')
                ->whereIn('id', $serviceIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            $preparedItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $serviceId = (int) ($item['service_id'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 0);
                $service = $services->get($serviceId);

                if (! $service || $qty <= 0) {
                    throw new InvalidArgumentException('Layanan tidak valid atau volume cucian harus lebih dari 0.');
                }

                $itemSubtotal = (int) round($service->price * $qty);
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'service_id' => $service->id,
                    'category_name' => $service->category?->name ?? '-',
                    'service_name' => $service->name,
                    'unit' => $service->unit->value,
                    'unit_price' => $service->price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $safeDiscountValue = max(0, $discountValue);
            $discountAmount = match ($discountType) {
                DiscountTypeEnum::NONE => 0,
                DiscountTypeEnum::NOMINAL => min($subtotal, $safeDiscountValue),
                DiscountTypeEnum::PERCENT => (int) round($subtotal * min(100, $safeDiscountValue) / 100),
            };

            $safeExtraWaterFee = max(0, $extraWaterFee);
            $safeAssistantFee = max(0, $assistantFee);
            $grandTotal = max(0, $subtotal - $discountAmount + $safeExtraWaterFee + $safeAssistantFee);
            $pointsEarned = intdiv($grandTotal, self::RUPIAH_PER_POINT);

            $transaction = LaundryTransaction::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $lockedCustomer->id,
                'user_id' => $cashier->id,
                'subtotal' => $subtotal,
                'discount_type' => $discountType->value,
                'discount_value' => $discountType === DiscountTypeEnum::NONE ? 0 : $safeDiscountValue,
                'discount_amount' => $discountAmount,
                'extra_water_fee' => $safeExtraWaterFee,
                'assistant_fee' => $safeAssistantFee,
                'grand_total' => $grandTotal,
                'points_earned' => $pointsEarned,
                'payment_status' => $paymentStatus->value,
                'payment_method' => $paymentMethod->value,
                'laundry_status' => LaundryStatusEnum::RECEIVED->value,
                'notes' => $notes ? trim($notes) : null,
            ]);

            foreach ($preparedItems as $preparedItem) {
                LaundryTransactionItem::create([
                    'laundry_transaction_id' => $transaction->id,
                    ...$preparedItem,
                ]);
            }

            if ($pointsEarned > 0) {
                $balanceBefore = (int) $lockedCustomer->points;
                $balanceAfter = $balanceBefore + $pointsEarned;

                $lockedCustomer->update(['points' => $balanceAfter]);

                PointHistory::create([
                    'customer_id' => $lockedCustomer->id,
                    'laundry_transaction_id' => $transaction->id,
                    'user_id' => $cashier->id,
                    'type' => PointTransactionTypeEnum::EARNED->value,
                    'points' => $pointsEarned,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reason' => 'Otomatis dari transaksi ' . $transaction->invoice_number,
                    'created_at' => now(),
                ]);
            }

            return $transaction->load(['customer', 'user', 'items']);
        });
    }

    /**
     * Menghasilkan nomor nota unik berformat LDR-YYYYMMDD-XXXX.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'LDR-' . now()->format('Ymd') . '-';
        $latestInvoice = LaundryTransaction::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('invoice_number');

        $nextNumber = 1;
        if (is_string($latestInvoice)) {
            $lastSequence = (int) substr($latestInvoice, strlen($prefix));
            $nextNumber = $lastSequence + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
