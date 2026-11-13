<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PointTransactionTypeEnum;
use App\Models\Customer;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LoyaltyPointService
{
    /**
     * Menambah atau mengurangi poin loyalitas pelanggan secara manual oleh admin dengan pencatatan histori.
     */
    public function adjustPoints(
        Customer $customer,
        User $admin,
        PointTransactionTypeEnum $type,
        int $points,
        string $reason
    ): PointHistory {
        if ($points <= 0) {
            throw new InvalidArgumentException('Jumlah poin harus lebih dari 0.');
        }

        if ($type === PointTransactionTypeEnum::EARNED) {
            throw new InvalidArgumentException('Penyesuaian manual hanya mendukung penambahan atau pengurangan poin.');
        }

        return DB::transaction(function () use ($customer, $admin, $type, $points, $reason): PointHistory {
            $lockedCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $balanceBefore = (int) $lockedCustomer->points;

            if ($type === PointTransactionTypeEnum::MANUAL_DEDUCT && $balanceBefore < $points) {
                throw new InvalidArgumentException('Saldo poin pelanggan tidak mencukupi untuk dikurangi.');
            }

            $signedPoints = $type === PointTransactionTypeEnum::MANUAL_ADD ? $points : -$points;
            $balanceAfter = $balanceBefore + $signedPoints;

            $lockedCustomer->update(['points' => $balanceAfter]);

            return PointHistory::create([
                'customer_id' => $lockedCustomer->id,
                'laundry_transaction_id' => null,
                'user_id' => $admin->id,
                'type' => $type->value,
                'points' => $signedPoints,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => trim($reason),
                'created_at' => now(),
            ]);
        });
    }
}
