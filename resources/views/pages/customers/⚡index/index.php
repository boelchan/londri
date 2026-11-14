<?php

declare(strict_types=1);

use App\Enums\PointTransactionTypeEnum;
use App\Livewire\Traits\WithTableX;
use App\Models\Customer;
use App\Models\PointHistory;
use App\Services\LoyaltyPointService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Pelanggan & Poin Loyalitas')] class extends Component
{
    use Interactions, WithTableX;

    public string $sortFieldDefault = 'name';
    public string $sortDirectionDefault = 'asc';

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'phone', 'points'];

    #[Url]
    public string $search_name = '';

    #[Url]
    public string $search_phone = '';

    /* Modal Tambah / Ubah Pelanggan */
    public bool $customerModal = false;
    public ?int $editingCustomerId = null;
    public string $name = '';
    public string $phone = '';
    public string $address = '';

    /* Modal Penyesuaian Poin Manual (Khusus Admin) */
    public bool $pointModal = false;
    public ?int $pointCustomerId = null;
    public string $pointCustomerName = '';
    public int $pointCustomerBalance = 0;
    public string $point_type = PointTransactionTypeEnum::MANUAL_ADD->value;
    public int|string $point_amount = 1;
    public string $point_reason = '';

    /* Modal Riwayat Mutasi Poin */
    public bool $historyModal = false;
    public ?int $historyCustomerId = null;
    public string $historyCustomerName = '';

    /**
     * Mengambil daftar pelanggan beserta jumlah transaksi dan total poin.
     */
    #[Computed]
    public function dataTable(): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('laundryTransactions')
            ->when($this->search_name !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search_name . '%'))
            ->when($this->search_phone !== '', fn ($q) => $q->where('phone', 'like', '%' . $this->search_phone . '%'))
            ->orderBy($this->safeSortField(), $this->safeSortDirection())
            ->paginate($this->perPage);
    }

    /**
     * Mengambil opsi penyesuaian poin manual untuk modal admin.
     *
     * @return array<int, array{label: string, value: string}>
     */
    #[Computed]
    public function manualPointOptions(): array
    {
        return [
            [
                'label' => PointTransactionTypeEnum::MANUAL_ADD->label(),
                'value' => PointTransactionTypeEnum::MANUAL_ADD->value,
            ],
            [
                'label' => PointTransactionTypeEnum::MANUAL_DEDUCT->label(),
                'value' => PointTransactionTypeEnum::MANUAL_DEDUCT->value,
            ],
        ];
    }

    /**
     * Mengambil riwayat mutasi poin untuk pelanggan yang sedang dilihat.
     *
     * @return Collection<int, PointHistory>
     */
    #[Computed]
    public function selectedPointHistories(): Collection
    {
        if (! $this->historyCustomerId) {
            return new Collection();
        }

        return PointHistory::with(['user', 'transaction'])
            ->where('customer_id', $this->historyCustomerId)
            ->latest('created_at')
            ->limit(30)
            ->get();
    }

    /**
     * Membuka modal untuk menambah pelanggan baru.
     */
    public function openCreateModal(): void
    {
        $this->reset(['editingCustomerId', 'name', 'phone', 'address']);
        $this->resetValidation();
        $this->customerModal = true;
    }

    /**
     * Membuka modal untuk mengubah data pelanggan yang sudah ada.
     */
    public function openEditModal(int $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        $this->editingCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->address = (string) ($customer->address ?? '');
        $this->resetValidation();
        $this->customerModal = true;
    }

    /**
     * Menyimpan data pelanggan baru atau memperbarui pelanggan yang ada.
     */
    public function saveCustomer(): void
    {
        $uniquePhoneRule = 'unique:customers,phone';
        if ($this->editingCustomerId) {
            $uniquePhoneRule .= ',' . $this->editingCustomerId;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:150',
            'phone' => ['required', 'string', 'max:30', $uniquePhoneRule],
            'address' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.unique' => 'Nomor telepon sudah digunakan pelanggan lain.',
        ]);

        if ($this->editingCustomerId) {
            $customer = Customer::query()->findOrFail($this->editingCustomerId);
            $customer->update([
                'name' => trim($validated['name']),
                'phone' => trim($validated['phone']),
                'address' => $validated['address'] ? trim($validated['address']) : null,
            ]);
            $this->toast()->success('Berhasil', 'Data pelanggan berhasil diperbarui.')->send();
        } else {
            Customer::create([
                'name' => trim($validated['name']),
                'phone' => trim($validated['phone']),
                'address' => $validated['address'] ? trim($validated['address']) : null,
                'points' => 0,
            ]);
            $this->toast()->success('Berhasil', 'Pelanggan baru berhasil ditambahkan.')->send();
        }

        $this->customerModal = false;
    }

    /**
     * Membuka modal penyesuaian poin manual khusus untuk Pemilik (Admin).
     */
    public function openPointModal(int $id): void
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $customer = Customer::query()->findOrFail($id);
        $this->pointCustomerId = $customer->id;
        $this->pointCustomerName = $customer->name;
        $this->pointCustomerBalance = (int) $customer->points;
        $this->point_type = PointTransactionTypeEnum::MANUAL_ADD->value;
        $this->point_amount = 1;
        $this->point_reason = '';
        $this->resetValidation();
        $this->pointModal = true;
    }

    /**
     * Menyimpan penyesuaian poin manual oleh Pemilik (Admin).
     */
    public function savePointAdjustment(LoyaltyPointService $pointService): void
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'pointCustomerId' => 'required|exists:customers,id',
            'point_type' => 'required|in:' . implode(',', [
                PointTransactionTypeEnum::MANUAL_ADD->value,
                PointTransactionTypeEnum::MANUAL_DEDUCT->value,
            ]),
            'point_amount' => 'required|integer|min:1|max:100000',
            'point_reason' => 'required|string|min:3|max:255',
        ], [
            'point_amount.required' => 'Jumlah poin wajib diisi.',
            'point_amount.min' => 'Jumlah poin minimal 1.',
            'point_reason.required' => 'Alasan penyesuaian poin wajib diisi.',
            'point_reason.min' => 'Alasan minimal 3 karakter.',
        ]);

        $customer = Customer::query()->findOrFail($this->pointCustomerId);
        $type = PointTransactionTypeEnum::from($this->point_type);

        try {
            $pointService->adjustPoints(
                customer: $customer,
                admin: Auth::user(),
                type: $type,
                points: (int) $this->point_amount,
                reason: $this->point_reason,
            );
        } catch (\InvalidArgumentException $e) {
            $this->toast()->error('Gagal', $e->getMessage())->send();
            return;
        }

        $this->pointModal = false;
        $this->toast()->success('Berhasil', 'Poin pelanggan berhasil disesuaikan.')->send();
    }

    /**
     * Membuka modal riwayat mutasi poin pelanggan.
     */
    public function openHistoryModal(int $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        $this->historyCustomerId = $customer->id;
        $this->historyCustomerName = $customer->name;
        unset($this->selectedPointHistories);
        $this->historyModal = true;
    }
};
