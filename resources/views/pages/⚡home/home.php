<?php

declare(strict_types=1);

use App\Enums\LaundryStatusEnum;
use App\Models\Customer;
use App\Models\LaundryTransaction;
use App\Models\PriceHistory;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Beranda Operasional')] class extends Component
{
    /**
     * Mengambil ringkasan metrik operasional hari ini.
     *
     * @return array{
     *     today_count: int,
     *     today_revenue: int,
     *     in_progress_count: int,
     *     ready_count: int,
     *     total_customers: int
     * }
     */
    #[Computed]
    public function summary(): array
    {
        $today = today();

        return [
            'today_count' => LaundryTransaction::query()->whereDate('created_at', $today)->count(),
            'today_revenue' => (int) LaundryTransaction::query()->whereDate('created_at', $today)->sum('grand_total'),
            'in_progress_count' => LaundryTransaction::query()
                ->whereIn('laundry_status', [
                    LaundryStatusEnum::RECEIVED->value,
                    LaundryStatusEnum::WASHING->value,
                    LaundryStatusEnum::IRONING->value,
                ])
                ->count(),
            'ready_count' => LaundryTransaction::query()
                ->where('laundry_status', LaundryStatusEnum::READY->value)
                ->count(),
            'total_customers' => Customer::query()->count(),
        ];
    }

    /**
     * Mengambil daftar 8 transaksi terbaru untuk dipantau langsung di beranda.
     *
     * @return Collection<int, LaundryTransaction>
     */
    #[Computed]
    public function recentTransactions(): Collection
    {
        return LaundryTransaction::with(['customer', 'user', 'items'])
            ->latest()
            ->limit(8)
            ->get();
    }

    /**
     * Mengambil 5 riwayat perubahan harga terbaru khusus untuk Pemilik (Admin).
     *
     * @return Collection<int, PriceHistory>
     */
    #[Computed]
    public function recentPriceHistories(): Collection
    {
        if (! auth()->user()?->isAdmin()) {
            return new Collection();
        }

        return PriceHistory::with('user')
            ->latest('created_at')
            ->limit(5)
            ->get();
    }
};
