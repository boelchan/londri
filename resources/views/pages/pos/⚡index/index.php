<?php

declare(strict_types=1);

use App\Enums\DiscountTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\LaundryTransaction;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\LaundryTransactionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Kasir - Input Transaksi')] class extends Component
{
    use Interactions;

    /* Pelanggan */
    public ?int $customer_id = null;

    /* Modal Tambah Pelanggan Cepat */
    public bool $customerModal = false;
    public string $new_customer_name = '';
    public string $new_customer_phone = '';
    public string $new_customer_address = '';

    /* Daftar Item Layanan dalam Nota (minimal 1 baris) */
    /** @var array<int, array{category_id: ?int, service_id: ?int, quantity: string|int|float}> */
    public array $rows = [];

    /* Diskon & Biaya Tambahan */
    public string $discount_type = DiscountTypeEnum::NONE->value;
    public string|int|float $discount_value = 0;
    public string|int|float $extra_water_fee = 0;
    public string|int|float $assistant_fee = 0;

    /* Pembayaran & Catatan */
    public string $payment_status = PaymentStatusEnum::PAID->value;
    public string $payment_method = PaymentMethodEnum::CASH->value;
    public string $notes = '';

    /* Rekap Per Kasir Hari Ini */
    public ?int $recap_cashier_id = null;

    /**
     * Menyiapkan satu baris layanan kosong saat halaman kasir dibuka.
     */
    public function mount(): void
    {
        $this->recap_cashier_id = Auth::id();
        $firstCategoryId = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->value('id');

        $this->rows = [
            [
                'category_id' => $firstCategoryId ? (int) $firstCategoryId : null,
                'service_id' => null,
                'quantity' => 1,
            ],
        ];
    }

    /**
     * Mengambil daftar pelanggan untuk pilihan dropdown searchable.
     *
     * @return array<int, array{label: string, value: int}>
     */
    #[Computed]
    public function customerOptions(): array
    {
        return Customer::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer): array => [
                'label' => $customer->name,
                'value' => $customer->id,
            ])
            ->all();
    }

    /**
     * Mengambil model pelanggan yang sedang dipilih.
     */
    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        if (! $this->customer_id) {
            return null;
        }

        return Customer::query()->find($this->customer_id);
    }

    /**
     * Mengambil daftar kategori layanan aktif.
     *
     * @return Collection<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil opsi kategori untuk select TallStackUI.
     *
     * @return array<int, array{label: string, value: int}>
     */
    #[Computed]
    public function categoryOptions(): array
    {
        return $this->categories
            ->map(fn (ServiceCategory $category): array => [
                'label' => $category->name,
                'value' => $category->id,
            ])
            ->all();
    }

    /**
     * Mengambil seluruh layanan aktif beserta kategorinya.
     *
     * @return Collection<int, Service>
     */
    #[Computed]
    public function activeServices(): Collection
    {
        return Service::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil opsi layanan berdasarkan kategori yang dipilih pada baris tertentu.
     *
     * @return array<int, array{label: string, value: int}>
     */
    public function serviceOptionsForCategory(?int $categoryId): array
    {
        return $this->activeServices
            ->when($categoryId, fn ($collection) => $collection->where('service_category_id', $categoryId))
            ->map(fn (Service $service): array => [
                'label' => $service->name . ' (' . number_format($service->price, 0, ',', '.') . '/' . $service->unit->shortLabel() . ')',
                'value' => $service->id,
            ])
            ->values()
            ->all();
    }

    /**
     * Mereset pilihan layanan jika kategori pada baris tersebut berubah.
     */
    public function updatedRows(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.category_id')) {
            $index = (int) explode('.', $key)[0];
            if (isset($this->rows[$index])) {
                $this->rows[$index]['service_id'] = null;
            }
        }
    }

    /**
     * Mereset nilai diskon saat tipe diskon diubah ke Tanpa Diskon.
     */
    public function updatedDiscountType(string $value): void
    {
        if ($value === DiscountTypeEnum::NONE->value) {
            $this->discount_value = 0;
        }
    }

    /**
     * Menambahkan baris layanan baru ke dalam nota transaksi.
     */
    public function addRow(): void
    {
        $firstCategoryId = $this->categories->first()?->id;

        $this->rows[] = [
            'category_id' => $firstCategoryId ? (int) $firstCategoryId : null,
            'service_id' => null,
            'quantity' => 1,
        ];
    }

    /**
     * Menghapus baris layanan dari nota transaksi (minimal tersisa satu baris).
     */
    public function removeRow(int $index): void
    {
        if (count($this->rows) <= 1) {
            return;
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    /**
     * Menghitung ringkasan biaya dan estimasi poin secara real-time.
     *
     * @return array{
     *     subtotal: int,
     *     discount_amount: int,
     *     extra_water_fee: int,
     *     assistant_fee: int,
     *     grand_total: int,
     *     points_earned: int
     * }
     */
    #[Computed]
    public function summary(): array
    {
        $service = app(LaundryTransactionService::class);

        $validItems = collect($this->rows)
            ->filter(fn (array $row): bool => ! empty($row['service_id']) && (float) ($row['quantity'] ?? 0) > 0)
            ->map(fn (array $row): array => [
                'service_id' => (int) $row['service_id'],
                'quantity' => (float) $row['quantity'],
            ])
            ->values()
            ->all();

        $discountType = DiscountTypeEnum::tryFrom($this->discount_type) ?? DiscountTypeEnum::NONE;

        return $service->calculateSummary(
            items: $validItems,
            discountType: $discountType,
            discountValue: $this->parseMoney($this->discount_value),
            extraWaterFee: $this->parseMoney($this->extra_water_fee),
            assistantFee: $this->parseMoney($this->assistant_fee),
        );
    }

    /**
     * Menyimpan pelanggan baru secara cepat langsung dari halaman kasir.
     */
    public function saveQuickCustomer(): void
    {
        $validated = $this->validate([
            'new_customer_name' => 'required|string|max:150',
            'new_customer_phone' => 'required|string|max:30|unique:customers,phone',
            'new_customer_address' => 'nullable|string|max:500',
        ], [
            'new_customer_name.required' => 'Nama pelanggan wajib diisi.',
            'new_customer_phone.required' => 'Nomor telepon pelanggan wajib diisi.',
            'new_customer_phone.unique' => 'Nomor telepon ini sudah terdaftar.',
        ]);

        $customer = Customer::create([
            'name' => trim($validated['new_customer_name']),
            'phone' => trim($validated['new_customer_phone']),
            'address' => $validated['new_customer_address'] ? trim($validated['new_customer_address']) : null,
            'points' => 0,
        ]);

        $this->customer_id = $customer->id;
        $this->customerModal = false;
        $this->reset(['new_customer_name', 'new_customer_phone', 'new_customer_address']);
        unset($this->customerOptions, $this->selectedCustomer);

        $this->toast()->success('Berhasil', 'Pelanggan baru berhasil ditambahkan dan dipilih.')->send();
    }

    /**
     * Memfinalisasi dan menyimpan transaksi laundry serta mengarahkan ke nota cetak.
     */
    public function submitTransaction(LaundryTransactionService $transactionService): void
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'rows' => 'required|array|min:1',
            'rows.*.service_id' => 'required|exists:services,id',
            'rows.*.quantity' => 'required|numeric|min:0.1',
            'discount_type' => 'required|in:' . implode(',', DiscountTypeEnum::values()),
            'payment_status' => 'required|in:' . implode(',', PaymentStatusEnum::values()),
            'payment_method' => 'required|in:' . implode(',', PaymentMethodEnum::values()),
            'notes' => 'nullable|string|max:1000',
        ], [
            'customer_id.required' => 'Pilih pelanggan atau tambahkan pelanggan baru terlebih dahulu.',
            'rows.*.service_id.required' => 'Jenis layanan wajib dipilih.',
            'rows.*.quantity.required' => 'Volume (kg/qty) wajib diisi.',
            'rows.*.quantity.min' => 'Volume minimal 0.1.',
        ]);

        $customer = Customer::query()->findOrFail($this->customer_id);
        $discountType = DiscountTypeEnum::from($this->discount_type);
        $paymentStatus = PaymentStatusEnum::from($this->payment_status);
        $paymentMethod = PaymentMethodEnum::from($this->payment_method);

        $items = array_map(fn (array $row): array => [
            'service_id' => (int) $row['service_id'],
            'quantity' => (float) $row['quantity'],
        ], $this->rows);

        try {
            $transaction = $transactionService->createTransaction(
                customer: $customer,
                cashier: Auth::user(),
                items: $items,
                discountType: $discountType,
                discountValue: $this->parseMoney($this->discount_value),
                extraWaterFee: $this->parseMoney($this->extra_water_fee),
                assistantFee: $this->parseMoney($this->assistant_fee),
                paymentStatus: $paymentStatus,
                paymentMethod: $paymentMethod,
                notes: $this->notes !== '' ? $this->notes : null,
            );
        } catch (\InvalidArgumentException $e) {
            $this->toast()->error('Gagal', $e->getMessage())->send();
            return;
        }

        $this->toast()->success('Transaksi Berhasil', 'Nota ' . $transaction->invoice_number . ' berhasil disimpan.')->send();
        $this->redirectRoute('transactions.show', ['transaction' => $transaction->id], navigate: true);
    }

    /**
     * Mengubah input nominal terformat menjadi integer rupiah.
     */
    private function parseMoney(string|int|float|null $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        return $digits === '' || $digits === null ? 0 : (int) $digits;
    }

    /**
     * Mengambil daftar kasir untuk filter rekap di menu kasir (khusus Pemilik dapat memilih kasir lain).
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
                'label' => $user->name,
                'value' => $user->id,
            ])
            ->all();
    }

    /**
     * Mengambil ringkasan transaksi hari ini untuk kasir yang dipilih.
     *
     * @return array{
     *     cashier_name: string,
     *     trx_count: int,
     *     paid_total: int,
     *     unpaid_total: int,
     *     cash_total: int,
     *     non_cash_total: int
     * }
     */
    #[Computed]
    public function cashierTodaySummary(): array
    {
        $targetUserId = Auth::user()?->isAdmin() && $this->recap_cashier_id
            ? $this->recap_cashier_id
            : Auth::id();

        $targetUser = User::query()->find($targetUserId);

        $base = LaundryTransaction::query()
            ->where('user_id', $targetUserId)
            ->whereDate('created_at', today());

        $trxCount = (clone $base)->count();
        $paidTotal = (int) (clone $base)->where('payment_status', PaymentStatusEnum::PAID->value)->sum('grand_total');
        $unpaidTotal = (int) (clone $base)->where('payment_status', PaymentStatusEnum::UNPAID->value)->sum('grand_total');
        $cashTotal = (int) (clone $base)
            ->where('payment_status', PaymentStatusEnum::PAID->value)
            ->where('payment_method', PaymentMethodEnum::CASH->value)
            ->sum('grand_total');

        return [
            'cashier_name' => $targetUser?->name ?? Auth::user()?->name ?? '-',
            'trx_count' => $trxCount,
            'paid_total' => $paidTotal,
            'unpaid_total' => $unpaidTotal,
            'cash_total' => $cashTotal,
            'non_cash_total' => $paidTotal - $cashTotal,
        ];
    }

    /**
     * Mengambil daftar transaksi hari ini milik kasir yang dipilih.
     *
     * @return Collection<int, LaundryTransaction>
     */
    #[Computed]
    public function cashierTodayTransactions(): Collection
    {
        $targetUserId = Auth::user()?->isAdmin() && $this->recap_cashier_id
            ? $this->recap_cashier_id
            : Auth::id();

        return LaundryTransaction::with('customer')
            ->where('user_id', $targetUserId)
            ->whereDate('created_at', today())
            ->latest()
            ->get();
    }
};
