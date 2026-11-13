<div class="space-y-6">
    <div class="flex justify-between items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Pelanggan &amp; Poin Loyalitas</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Pelanggan &amp; Poin</span>
            </nav>
        </div>

        <x-button
            color="emerald"
            sm
            type="button"
            wire:click="openCreateModal"
            title="Tambah Pelanggan"
            aria-label="Tambah Pelanggan"
        >
            <x-ti-user-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
            <span class="hidden sm:inline">Tambah Pelanggan</span>
        </x-button>
    </div>

    <x-card class="bg-white border border-slate-200/80 shadow-xs">
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-800 text-sm">Pencarian Pelanggan</span>
                <x-button.circle color="red" flat sm type="button" wire:click="resetFilters" title="Reset filter" aria-label="Reset filter">
                    <x-ti-filter-x class="size-4" aria-hidden="true" />
                </x-button.circle>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input clearable label="Nama Pelanggan" placeholder="Cari nama..." wire:model.live.debounce.400ms="search_name" />
            <x-input clearable label="Nomor Telepon" placeholder="Cari nomor telepon..." wire:model.live.debounce.400ms="search_phone" />
        </div>
    </x-card>

    <x-table :paginate="$this->dataTable">
        <x-table.thead class="bg-slate-100" :sortDirection="$sortDirection" :sortField="$sortField">
            <x-table.th label="No" width="5%" />
            <x-table.th label="Nama Pelanggan" sort="name" />
            <x-table.th label="Telepon" sort="phone" />
            <x-table.th label="Alamat" />
            <x-table.th label="Transaksi" />
            <x-table.th label="Poin Loyalitas" sort="points" />
            <x-table.th label="Aksi" />
        </x-table.thead>

        <tbody>
            @forelse ($this->dataTable as $index => $d)
                <tr class="bg-white hover:bg-slate-50 transition-colors" wire:key="cust-{{ $d->id }}">
                    <td class="p-3 text-center text-slate-600 text-sm">
                        {{ $perPage * ($this->dataTable->currentPage() - 1) + $index + 1 }}
                    </td>
                    <td class="p-3 text-sm font-semibold text-slate-900">{{ $d->name }}</td>
                    <td class="p-3 text-sm text-slate-700 tabular-nums">{{ $d->phone }}</td>
                    <td class="p-3 text-sm text-slate-600">{{ $d->address ?: '-' }}</td>
                    <td class="p-3 text-sm text-slate-700 tabular-nums">{{ $d->laundry_transactions_count }} nota</td>
                    <td class="p-3 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 tabular-nums">
                            <x-ti-coins class="size-3.5 text-emerald-600" aria-hidden="true" />
                            {{ number_format($d->points, 0, ',', '.') }} Poin
                        </span>
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <x-button.circle
                                color="emerald"
                                sm
                                type="button"
                                wire:click="openEditModal({{ $d->id }})"
                                title="Ubah Pelanggan"
                                aria-label="Ubah {{ $d->name }}"
                            >
                                <x-ti-edit class="size-4" aria-hidden="true" />
                            </x-button.circle>

                            <x-button.circle
                                color="sky"
                                outline
                                sm
                                type="button"
                                wire:click="openHistoryModal({{ $d->id }})"
                                title="Riwayat Poin"
                                aria-label="Riwayat Poin {{ $d->name }}"
                            >
                                <x-ti-history class="size-4" aria-hidden="true" />
                            </x-button.circle>

                            @if (auth()->user()?->isAdmin())
                                <x-button
                                    color="amber"
                                    outline
                                    xs
                                    type="button"
                                    wire:click="openPointModal({{ $d->id }})"
                                    title="Sesuaikan Poin"
                                    aria-label="Sesuaikan Poin {{ $d->name }}"
                                >
                                    <x-ti-coins class="size-3.5 sm:mr-1 inline" aria-hidden="true" />
                                    <span class="hidden sm:inline">Sesuaikan Poin</span>
                                </x-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="p-6 text-center text-sm text-slate-500" colspan="7">
                        Belum ada data pelanggan yang cocok dengan pencarian.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Tambah / Ubah Pelanggan --}}
    <x-modal wire="customerModal" :title="$editingCustomerId ? 'Ubah Data Pelanggan' : 'Tambah Pelanggan Baru'" size="md" center="sm">
        <form wire:submit="saveCustomer" class="space-y-4">
            <x-input label="Nama Lengkap" placeholder="Nama pelanggan" wire:model="name" />
            <x-input label="Nomor Telepon" placeholder="0812..." wire:model="phone" />
            <x-textarea label="Alamat (Opsional)" placeholder="Alamat pelanggan" wire:model="address" rows="2" />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('customerModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="saveCustomer">
                    Simpan
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Penyesuaian Poin Manual (Khusus Pemilik/Admin) --}}
    <x-modal wire="pointModal" title="Penyesuaian Poin Loyalitas" size="md" center="sm">
        <form wire:submit="savePointAdjustment" class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-sm">
                <p class="font-semibold text-slate-900">{{ $pointCustomerName }}</p>
                <p class="text-xs text-slate-600 mt-0.5">
                    Saldo poin saat ini: <strong class="text-emerald-700">{{ number_format($pointCustomerBalance, 0, ',', '.') }} Poin</strong>
                </p>
            </div>

            <x-select.styled
                label="Jenis Penyesuaian"
                wire:model="point_type"
                :options="$this->manualPointOptions"
                select="label:label|value:value"
            />

            <x-number
                label="Jumlah Poin"
                wire:model="point_amount"
                :min="1"
                :step="1"
            />

            <x-input
                label="Alasan / Keterangan"
                placeholder="Misal: Kompensasi keterlambatan atau penukaran voucher cuci gratis"
                wire:model="point_reason"
            />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('pointModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="savePointAdjustment">
                    Simpan Penyesuaian
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Riwayat Mutasi Poin --}}
    <x-modal wire="historyModal" :title="'Riwayat Poin: ' . $historyCustomerName" size="lg" center="sm">
        @if ($this->selectedPointHistories->isEmpty())
            <p class="py-6 text-center text-sm text-slate-500">
                Belum ada riwayat mutasi poin untuk pelanggan ini.
            </p>
        @else
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @foreach ($this->selectedPointHistories as $log)
                    <div class="py-3 flex items-start justify-between gap-3 text-sm" wire:key="pt-log-{{ $log->id }}">
                        <div>
                            <div class="flex items-center gap-2">
                                <x-badge :text="$log->type->label()" :color="$log->type->color()" light xs />
                                <span class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-1 text-slate-800">{{ $log->reason ?: '-' }}</p>
                            <p class="text-xs text-slate-500">Oleh: {{ $log->user?->name ?? '-' }}</p>
                        </div>
                        <div class="text-right shrink-0 tabular-nums">
                            <p class="font-bold {{ $log->points >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $log->points >= 0 ? '+' . $log->points : $log->points }} Poin
                            </p>
                            <p class="text-xs text-slate-500">
                                Saldo: {{ $log->balance_before }} &rarr; {{ $log->balance_after }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-slot:footer>
            <x-button type="button" color="slate" outline wire:click="$set('historyModal', false)">
                Tutup
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>
