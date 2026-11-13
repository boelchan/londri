<div class="space-y-6">
    <div class="flex justify-between items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Kategori &amp; Jenis Layanan</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span>Panel Pemilik</span>
                <span>/</span>
                <span class="text-slate-700 font-medium">Kategori &amp; Layanan</span>
            </nav>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-button
                color="emerald"
                sm
                type="button"
                wire:click="openCreateCategory"
                title="Tambah Kategori"
                aria-label="Tambah Kategori"
            >
                <x-ti-folder-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Tambah Kategori</span>
            </x-button>
        </div>
    </div>

    {{-- Pencarian Kategori --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs">
        <x-input
            clearable
            label="Cari Kategori Layanan"
            placeholder="Misal: Kiloan, Satuan, Karpet..."
            wire:model.live.debounce.300ms="search"
        />
    </x-card>

    {{-- Daftar Kategori Ringkas --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs" paddingless>
        <div class="px-5 py-4 border-b border-slate-200/80">
            <h2 class="text-sm sm:text-base font-semibold text-slate-900">Daftar Kategori Cucian</h2>
            <p class="text-xs text-slate-500">Pilih tombol List Layanan pada kategori untuk mengelola tarif dan melihat histori harga.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 w-12 text-center">No</th>
                        <th class="px-5 py-3">Nama Kategori</th>
                        <th class="px-5 py-3">Daftar Layanan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($this->categories as $index => $cat)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="cat-row-{{ $cat->id }}">
                            <td class="px-5 py-3.5 text-center text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $cat->name }}</div>
                                @if ($cat->description)
                                    <div class="text-xs text-slate-500">{{ $cat->description }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($cat->services->isEmpty())
                                    <span class="text-xs text-slate-400">Belum ada layanan</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($cat->services as $svc)
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700">
                                                <span class="font-medium">{{ $svc->name }}</span>
                                                <span class="text-emerald-700 font-semibold tabular-nums">{{ number_format($svc->price, 0, ',', '.') }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($cat->is_active)
                                    <x-badge text="Aktif" color="emerald" light sm />
                                @else
                                    <x-badge text="Non-Aktif" color="red" light sm />
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <x-button
                                        color="emerald"
                                        xs
                                        type="button"
                                        wire:click="openServicesModal({{ $cat->id }})"
                                        title="List Layanan ({{ $cat->services_count }})"
                                        aria-label="List Layanan {{ $cat->name }} ({{ $cat->services_count }})"
                                    >
                                        <x-ti-list class="size-3.5 sm:mr-1 inline" aria-hidden="true" />
                                        <span class="hidden sm:inline">List Layanan ({{ $cat->services_count }})</span>
                                    </x-button>

                                    <x-button.circle
                                        color="slate"
                                        outline
                                        sm
                                        type="button"
                                        wire:click="openEditCategory({{ $cat->id }})"
                                        title="Ubah Kategori"
                                        aria-label="Ubah Kategori {{ $cat->name }}"
                                    >
                                        <x-ti-edit class="size-4" aria-hidden="true" />
                                    </x-button.circle>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-6 text-center text-sm text-slate-500" colspan="5">
                                Belum ada kategori layanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Modal List Layanan per Kategori --}}
    <x-modal wire="servicesModal" :title="'Layanan Kategori: ' . $selectedCategoryName" size="3xl" center="sm">
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-200">
                <p class="text-xs text-slate-600">
                    Kelola jenis layanan, ubah tarif harga, atau pantau histori perubahan harga layanan di kategori ini.
                </p>
                <x-button
                    color="emerald"
                    xs
                    type="button"
                    wire:click="openCreateService({{ $selectedCategoryId }})"
                    title="Tambah Layanan"
                    aria-label="Tambah Layanan"
                    class="shrink-0"
                >
                    <x-ti-plus class="size-3.5 sm:mr-1 inline" aria-hidden="true" />
                    <span class="hidden sm:inline">Tambah Layanan</span>
                </x-button>
            </div>

            @if ($this->selectedCategoryServices->isEmpty())
                <div class="py-8 text-center text-sm text-slate-500">
                    Belum ada jenis layanan di kategori ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-3 py-2.5">Nama Layanan</th>
                                <th class="px-3 py-2.5">Satuan</th>
                                <th class="px-3 py-2.5">Estimasi</th>
                                <th class="px-3 py-2.5 text-right">Tarif Aktif</th>
                                <th class="px-3 py-2.5">Status</th>
                                <th class="px-3 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($this->selectedCategoryServices as $svc)
                                <tr class="hover:bg-slate-50" wire:key="modal-svc-{{ $svc->id }}">
                                    <td class="px-3 py-3 font-semibold text-slate-900">{{ $svc->name }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $svc->unit->shortLabel() }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $svc->estimated_duration ?: '-' }}</td>
                                    <td class="px-3 py-3 text-right font-bold text-emerald-700 tabular-nums whitespace-nowrap">
                                        {{ number_format($svc->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @if ($svc->is_active)
                                            <x-badge text="Aktif" color="emerald" light xs />
                                        @else
                                            <x-badge text="Non-Aktif" color="red" light xs />
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <x-button
                                                color="emerald"
                                                outline
                                                xs
                                                type="button"
                                                wire:click="openEditService({{ $svc->id }})"
                                                title="Edit Harga"
                                                aria-label="Edit Harga {{ $svc->name }}"
                                            >
                                                <x-ti-edit class="size-3.5 sm:mr-1 inline" aria-hidden="true" />
                                                <span class="hidden sm:inline">Edit Harga</span>
                                            </x-button>

                                            <x-button
                                                color="sky"
                                                outline
                                                xs
                                                type="button"
                                                wire:click="openPriceHistoryModal({{ $svc->id }})"
                                                title="Histori Harga"
                                                aria-label="Histori Harga {{ $svc->name }}"
                                            >
                                                <x-ti-history class="size-3.5 sm:mr-1 inline" aria-hidden="true" />
                                                <span class="hidden sm:inline">Histori Harga</span>
                                            </x-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <x-slot:footer>
            <x-button type="button" color="slate" outline wire:click="$set('servicesModal', false)">
                Tutup
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Modal Kategori --}}
    <x-modal wire="categoryModal" :title="$editingCategoryId ? 'Ubah Kategori' : 'Tambah Kategori Baru'" size="md" center="sm">
        <form wire:submit="saveCategory" class="space-y-4">
            <x-input label="Nama Kategori" placeholder="Contoh: Kiloan, Satuan, Karpet" wire:model="cat_name" />
            <x-textarea label="Deskripsi (Opsional)" placeholder="Keterangan singkat kategori" wire:model="cat_description" rows="2" />
            <x-toggle label="Kategori Aktif" wire:model="cat_is_active" color="emerald" />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('categoryModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="saveCategory">
                    Simpan Kategori
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Tambah / Edit Harga Layanan --}}
    <x-modal wire="serviceModal" :title="$editingServiceId ? 'Edit Harga & Layanan' : 'Tambah Jenis Layanan Baru'" size="md" center="sm">
        <form wire:submit="saveService" class="space-y-4">
            <x-input label="Nama Jenis Layanan" placeholder="Contoh: Cuci Setrika Reguler" wire:model="svc_name" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select.styled
                    label="Satuan"
                    wire:model="svc_unit"
                    :options="\App\Enums\ServiceUnitEnum::choices()"
                    select="label:label|value:value"
                />

                <x-input
                    label="Estimasi Selesai (Opsional)"
                    placeholder="Contoh: 2 Hari, 6 Jam"
                    wire:model="svc_estimated_duration"
                />
            </div>

            <x-currency
                label="Tarif Harga Aktif"
                wire:model.live.debounce.250ms="svc_price"
                locale="id-ID"
                :decimals="0"
                :precision="0"
            />

            @if ($editingServiceId && $original_price !== null && (int) preg_replace('/[^0-9]/', '', (string) $svc_price) !== $original_price)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 space-y-2">
                    <p class="font-semibold">
                        Perubahan tarif: {{ number_format($original_price, 0, ',', '.') }} &rarr; {{ number_format((int) preg_replace('/[^0-9]/', '', (string) $svc_price), 0, ',', '.') }}
                    </p>
                    <x-input
                        label="Catatan Perubahan Harga (Opsional)"
                        placeholder="Misal: Penyesuaian biaya operasional / deterjen"
                        wire:model="price_change_notes"
                    />
                </div>
            @endif

            <x-toggle label="Layanan Aktif" wire:model="svc_is_active" color="emerald" />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('serviceModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="saveService">
                    Simpan
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Histori Harga per Layanan --}}
    <x-modal wire="priceHistoryModal" :title="'Histori Harga: ' . $historyServiceName" size="lg" center="sm">
        @if ($this->selectedServicePriceHistories->isEmpty())
            <p class="py-6 text-center text-sm text-slate-500">
                Belum ada riwayat perubahan harga untuk layanan ini.
            </p>
        @else
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @foreach ($this->selectedServicePriceHistories as $log)
                    @php
                        $diff = $log->new_price - $log->old_price;
                    @endphp
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm" wire:key="svc-ph-{{ $log->id }}">
                        <div>
                            <p class="text-xs text-slate-500">
                                {{ $log->created_at->format('d/m/Y H:i') }} &bull; Oleh: <strong class="text-slate-800">{{ $log->user?->name ?? '-' }}</strong>
                            </p>
                            @if ($log->notes)
                                <p class="mt-0.5 text-xs text-slate-700">{{ $log->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 tabular-nums shrink-0">
                            <span class="text-xs text-slate-500 line-through">{{ number_format($log->old_price, 0, ',', '.') }}</span>
                            <x-ti-arrow-right class="size-3.5 text-slate-400" aria-hidden="true" />
                            <span class="font-bold text-slate-900">{{ number_format($log->new_price, 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold {{ $diff >= 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                ({{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }})
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-slot:footer>
            <x-button type="button" color="slate" outline wire:click="$set('priceHistoryModal', false)">
                Tutup
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>
