<?php

declare(strict_types=1);

use App\Enums\ServiceUnitEnum;
use App\Models\PriceHistory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Kategori & Layanan Laundry')] class extends Component
{
    use Interactions;

    public string $search = '';

    /* Modal Kategori */
    public bool $categoryModal = false;
    public ?int $editingCategoryId = null;
    public string $cat_name = '';
    public string $cat_description = '';
    public bool $cat_is_active = true;

    /* Modal List Layanan per Kategori */
    public bool $servicesModal = false;
    public ?int $selectedCategoryId = null;
    public string $selectedCategoryName = '';

    /* Modal Tambah / Edit Harga Layanan */
    public bool $serviceModal = false;
    public ?int $editingServiceId = null;
    public ?int $svc_category_id = null;
    public string $svc_name = '';
    public string $svc_unit = ServiceUnitEnum::KG->value;
    public string|int|float $svc_price = 0;
    public string $svc_estimated_duration = '';
    public bool $svc_is_active = true;
    public string $price_change_notes = '';
    public ?int $original_price = null;

    /* Modal Histori Harga Layanan */
    public bool $priceHistoryModal = false;
    public ?int $historyServiceId = null;
    public string $historyServiceName = '';

    /**
     * Mengambil daftar seluruh kategori layanan beserta jumlah layanannya.
     *
     * @return Collection<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()
            ->with(['services' => fn ($q) => $q->orderBy('name')])
            ->withCount('services')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil daftar layanan untuk kategori yang sedang dibuka di modal.
     *
     * @return Collection<int, Service>
     */
    #[Computed]
    public function selectedCategoryServices(): Collection
    {
        if (! $this->selectedCategoryId) {
            return new Collection();
        }

        return Service::query()
            ->where('service_category_id', $this->selectedCategoryId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil riwayat perubahan harga untuk layanan yang dipilih.
     *
     * @return Collection<int, PriceHistory>
     */
    #[Computed]
    public function selectedServicePriceHistories(): Collection
    {
        if (! $this->historyServiceId) {
            return new Collection();
        }

        return PriceHistory::with('user')
            ->where('service_id', $this->historyServiceId)
            ->latest('created_at')
            ->limit(30)
            ->get();
    }

    /**
     * Membuka modal tambah kategori baru.
     */
    public function openCreateCategory(): void
    {
        $this->reset(['editingCategoryId', 'cat_name', 'cat_description']);
        $this->cat_is_active = true;
        $this->resetValidation();
        $this->categoryModal = true;
    }

    /**
     * Membuka modal ubah kategori.
     */
    public function openEditCategory(int $id): void
    {
        $category = ServiceCategory::query()->findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->cat_name = $category->name;
        $this->cat_description = (string) ($category->description ?? '');
        $this->cat_is_active = (bool) $category->is_active;
        $this->resetValidation();
        $this->categoryModal = true;
    }

    /**
     * Menyimpan kategori layanan baru atau perubahan kategori.
     */
    public function saveCategory(): void
    {
        $uniqueRule = 'unique:service_categories,name';
        if ($this->editingCategoryId) {
            $uniqueRule .= ',' . $this->editingCategoryId;
        }

        $validated = $this->validate([
            'cat_name' => ['required', 'string', 'max:100', $uniqueRule],
            'cat_description' => 'nullable|string|max:500',
            'cat_is_active' => 'boolean',
        ], [
            'cat_name.required' => 'Nama kategori wajib diisi.',
            'cat_name.unique' => 'Nama kategori sudah ada.',
        ]);

        if ($this->editingCategoryId) {
            $category = ServiceCategory::query()->findOrFail($this->editingCategoryId);
            $category->update([
                'name' => trim($validated['cat_name']),
                'description' => $validated['cat_description'] ? trim($validated['cat_description']) : null,
                'is_active' => $this->cat_is_active,
            ]);
            $this->toast()->success('Berhasil', 'Kategori berhasil diperbarui.')->send();
        } else {
            ServiceCategory::create([
                'name' => trim($validated['cat_name']),
                'description' => $validated['cat_description'] ? trim($validated['cat_description']) : null,
                'is_active' => $this->cat_is_active,
            ]);
            $this->toast()->success('Berhasil', 'Kategori baru berhasil ditambahkan.')->send();
        }

        $this->categoryModal = false;
        unset($this->categories);
    }

    /**
     * Membuka modal daftar layanan milik kategori tertentu.
     */
    public function openServicesModal(int $categoryId): void
    {
        $category = ServiceCategory::query()->findOrFail($categoryId);
        $this->selectedCategoryId = $category->id;
        $this->selectedCategoryName = $category->name;
        unset($this->selectedCategoryServices);
        $this->servicesModal = true;
    }

    /**
     * Membuka modal tambah jenis layanan baru di dalam kategori yang dipilih.
     */
    public function openCreateService(?int $categoryId = null): void
    {
        $this->reset(['editingServiceId', 'svc_name', 'svc_estimated_duration', 'price_change_notes', 'original_price']);
        $this->svc_category_id = $categoryId ?? $this->selectedCategoryId ?? $this->categories->first()?->id;
        $this->svc_unit = ServiceUnitEnum::KG->value;
        $this->svc_price = 0;
        $this->svc_is_active = true;
        $this->resetValidation();
        $this->serviceModal = true;
    }

    /**
     * Membuka modal edit harga dan detail layanan.
     */
    public function openEditService(int $id): void
    {
        $service = Service::query()->findOrFail($id);
        $this->editingServiceId = $service->id;
        $this->svc_category_id = $service->service_category_id;
        $this->svc_name = $service->name;
        $this->svc_unit = $service->unit->value;
        $this->svc_price = (int) $service->price;
        $this->original_price = (int) $service->price;
        $this->svc_estimated_duration = (string) ($service->estimated_duration ?? '');
        $this->svc_is_active = (bool) $service->is_active;
        $this->price_change_notes = '';
        $this->resetValidation();
        $this->serviceModal = true;
    }

    /**
     * Menyimpan layanan baru atau memperbarui tarif harga dengan pencatatan audit trail.
     */
    public function saveService(ServiceCatalogService $catalogService): void
    {
        $this->validate([
            'svc_category_id' => 'required|exists:service_categories,id',
            'svc_name' => 'required|string|max:120',
            'svc_unit' => 'required|in:' . implode(',', ServiceUnitEnum::values()),
            'svc_price' => 'required',
            'svc_estimated_duration' => 'nullable|string|max:60',
            'svc_is_active' => 'boolean',
            'price_change_notes' => 'nullable|string|max:255',
        ], [
            'svc_category_id.required' => 'Kategori layanan wajib dipilih.',
            'svc_name.required' => 'Nama jenis layanan wajib diisi.',
        ]);

        $priceInt = $this->parseMoney($this->svc_price);
        if ($priceInt < 0) {
            $this->addError('svc_price', 'Tarif harga tidak boleh negatif.');
            return;
        }

        $payload = [
            'service_category_id' => (int) $this->svc_category_id,
            'name' => trim($this->svc_name),
            'unit' => $this->svc_unit,
            'price' => $priceInt,
            'estimated_duration' => $this->svc_estimated_duration !== '' ? trim($this->svc_estimated_duration) : null,
            'is_active' => $this->svc_is_active,
        ];

        if ($this->editingServiceId) {
            $service = Service::query()->findOrFail($this->editingServiceId);
            $priceChanged = (int) $service->price !== $priceInt;

            $catalogService->updateService(
                service: $service,
                data: $payload,
                admin: Auth::user(),
                priceChangeNotes: $this->price_change_notes !== '' ? $this->price_change_notes : null,
            );

            $message = $priceChanged
                ? 'Tarif diperbarui dan perubahan harga dicatat ke histori.'
                : 'Data layanan berhasil diperbarui.';
            $this->toast()->success('Berhasil', $message)->send();
        } else {
            Service::create($payload);
            $this->toast()->success('Berhasil', 'Jenis layanan baru berhasil ditambahkan.')->send();
        }

        $this->serviceModal = false;
        unset($this->selectedCategoryServices, $this->categories);
    }

    /**
     * Membuka modal riwayat perubahan harga untuk layanan tertentu.
     */
    public function openPriceHistoryModal(int $serviceId): void
    {
        $service = Service::query()->findOrFail($serviceId);
        $this->historyServiceId = $service->id;
        $this->historyServiceName = $service->name;
        unset($this->selectedServicePriceHistories);
        $this->priceHistoryModal = true;
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
};
