<?php

declare(strict_types=1);

use App\Enums\PaymentStatusEnum;
use App\Livewire\Traits\WithTableX;
use App\Models\LaundryTransaction;
use App\Models\OfficeExpense;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Rekap Transaksi & Keuangan')] class extends Component
{
    use WithTableX;

    public string $sortFieldDefault = 'created_at';
    public string $sortDirectionDefault = 'desc';

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'invoice_number', 'payment_status', 'grand_total'];

    #[Url]
    public string $filter_month = '';

    #[Url]
    public string $filter_date_from = '';

    #[Url]
    public string $filter_date_to = '';

    #[Url]
    public ?int $filter_cashier_id = null;

    #[Url]
    public ?string $filter_payment_status = null;

    /**
     * Menetapkan bulan berjalan sebagai filter awal jika belum ada filter tanggal/bulan.
     */
    public function mount(): void
    {
        if ($this->filter_month === '' && $this->filter_date_from === '' && $this->filter_date_to === '') {
            $this->filter_month = now()->format('Y-m');
        }
    }

    /**
     * Mengembalikan seluruh filter ke bulan berjalan.
     */
    public function resetRecapFilters(): void
    {
        $this->filter_month = now()->format('Y-m');
        $this->filter_date_from = '';
        $this->filter_date_to = '';
        $this->filter_cashier_id = null;
        $this->filter_payment_status = null;
        $this->resetPage();
    }

    /**
     * Mereset halaman paginasi saat filter berubah.
     */
    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter_')) {
            $this->resetPage();
        }
    }

    /**
     * Mengambil opsi bulan (12 bulan terakhir) untuk filter rekap.
     *
     * @return array<int, array{label: string, value: string}>
     */
    #[Computed]
    public function monthOptions(): array
    {
        $options = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->startOfMonth()->subMonths($i);
            $options[] = [
                'label' => $date->translatedFormat('F Y'),
                'value' => $date->format('Y-m'),
            ];
        }

        return $options;
    }

    /**
     * Mengambil opsi daftar kasir/pengguna untuk filter per kasir.
     *
     * @return array<int, array{label: string, value: int}>
     */
    #[Computed]
    public function cashierOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'label' => $user->name . ' (' . ($user->isAdmin() ? 'Pemilik' : 'Kasir') . ')',
                'value' => $user->id,
            ])
            ->all();
    }

    /**
     * Membangun query dasar transaksi sesuai filter tanggal, bulan, kasir, dan status bayar.
     *
     * @return Builder<LaundryTransaction>
     */
    private function filteredTransactionQuery(): Builder
    {
        $hasDateRange = $this->filter_date_from !== '' || $this->filter_date_to !== '';
        $isAdmin = (bool) Auth::user()?->isAdmin();

        return LaundryTransaction::query()
            ->when(! $isAdmin, fn (Builder $q) => $q->where('user_id', Auth::id()))
            ->when(! $hasDateRange && $this->filter_month !== '', function (Builder $q): void {
                [$year, $month] = array_pad(explode('-', $this->filter_month), 2, null);
                if ($year && $month) {
                    $q->whereYear('created_at', (int) $year)->whereMonth('created_at', (int) $month);
                }
            })
            ->when($this->filter_date_from !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $this->filter_date_from))
            ->when($this->filter_date_to !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $this->filter_date_to))
            ->when($isAdmin && $this->filter_cashier_id, fn (Builder $q) => $q->where('user_id', $this->filter_cashier_id))
            ->when($this->filter_payment_status, fn (Builder $q) => $q->where('payment_status', $this->filter_payment_status));
    }

    /**
     * Menghitung ringkasan jumlah uang masuk, piutang, pengeluaran kantor, dan kas bersih sesuai filter.
     *
     * @return array{
     *     trx_count: int,
     *     paid_total: int,
     *     unpaid_total: int,
     *     gross_total: int,
     *     expense_total: int,
     *     net_cash: int
     * }
     */
    #[Computed]
    public function summary(): array
    {
        $base = $this->filteredTransactionQuery();

        $trxCount = (clone $base)->count();
        $paidTotal = (int) (clone $base)->where('payment_status', PaymentStatusEnum::PAID->value)->sum('grand_total');
        $unpaidTotal = (int) (clone $base)->where('payment_status', PaymentStatusEnum::UNPAID->value)->sum('grand_total');
        $grossTotal = $paidTotal + $unpaidTotal;

        $hasDateRange = $this->filter_date_from !== '' || $this->filter_date_to !== '';
        $expenseTotal = (int) OfficeExpense::query()
            ->when(! $hasDateRange && $this->filter_month !== '', function (Builder $q): void {
                [$year, $month] = array_pad(explode('-', $this->filter_month), 2, null);
                if ($year && $month) {
                    $q->whereYear('expense_date', (int) $year)->whereMonth('expense_date', (int) $month);
                }
            })
            ->when($this->filter_date_from !== '', fn (Builder $q) => $q->whereDate('expense_date', '>=', $this->filter_date_from))
            ->when($this->filter_date_to !== '', fn (Builder $q) => $q->whereDate('expense_date', '<=', $this->filter_date_to))
            ->sum('amount');

        return [
            'trx_count' => $trxCount,
            'paid_total' => $paidTotal,
            'unpaid_total' => $unpaidTotal,
            'gross_total' => $grossTotal,
            'expense_total' => $expenseTotal,
            'net_cash' => $paidTotal - $expenseTotal,
        ];
    }

    /**
     * Mengambil rekapitulasi transaksi dan uang masuk per kasir sesuai filter aktif.
     *
     * @return Collection<int, object{
     *     user_id: int,
     *     cashier_name: string,
     *     trx_count: int,
     *     paid_total: int,
     *     unpaid_total: int,
     *     gross_total: int
     * }>
     */
    #[Computed]
    public function cashierRecap(): Collection
    {
        $rows = (clone $this->filteredTransactionQuery())
            ->selectRaw('user_id, COUNT(*) as trx_count')
            ->selectRaw("SUM(CASE WHEN payment_status = ? THEN grand_total ELSE 0 END) as paid_total", [PaymentStatusEnum::PAID->value])
            ->selectRaw("SUM(CASE WHEN payment_status = ? THEN grand_total ELSE 0 END) as unpaid_total", [PaymentStatusEnum::UNPAID->value])
            ->selectRaw('SUM(grand_total) as gross_total')
            ->groupBy('user_id')
            ->get();

        $users = User::query()->whereIn('id', $rows->pluck('user_id'))->pluck('name', 'id');

        return $rows->map(fn ($row): object => (object) [
            'user_id' => (int) $row->user_id,
            'cashier_name' => (string) ($users[$row->user_id] ?? '-'),
            'trx_count' => (int) $row->trx_count,
            'paid_total' => (int) $row->paid_total,
            'unpaid_total' => (int) $row->unpaid_total,
            'gross_total' => (int) $row->gross_total,
        ])->sortByDesc('paid_total')->values();
    }

    /**
     * Mengambil daftar transaksi sesuai filter dengan paginasi.
     */
    #[Computed]
    public function dataTable(): LengthAwarePaginator
    {
        return $this->filteredTransactionQuery()
            ->with(['customer', 'user'])
            ->orderBy($this->safeSortField(), $this->safeSortDirection())
            ->paginate($this->perPage);
    }
};
