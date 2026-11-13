<div class="space-y-6">
    <div class="flex justify-between items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Pengeluaran Kantor</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span>Panel Pemilik</span>
                <span>/</span>
                <span class="text-slate-700 font-medium">Pengeluaran Kantor</span>
            </nav>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-button
                color="slate"
                outline
                sm
                href="{{ route('recap.index') }}"
                wire:navigate
                title="Lihat Rekap"
                aria-label="Lihat Rekap"
            >
                <x-ti-report-analytics class="size-4 sm:mr-1 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Lihat Rekap</span>
            </x-button>
            <x-button
                color="emerald"
                sm
                type="button"
                wire:click="openCreateModal"
                title="Catat Pengeluaran"
                aria-label="Catat Pengeluaran"
            >
                <x-ti-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Catat Pengeluaran</span>
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <x-card class="bg-rose-50/70 border border-rose-200 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-800">Total Pengeluaran (Filter)</p>
            <p class="mt-2 text-2xl font-bold text-rose-950 tabular-nums">
                {{ number_format($this->totalFilteredAmount, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-rose-800">Otomatis terhitung di Rekap Keuangan</p>
        </x-card>

        <x-card class="lg:col-span-3 bg-white border border-slate-200/80 shadow-xs">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-800 text-sm">Pencarian &amp; Filter Pengeluaran</span>
                    <x-button.circle color="red" flat sm type="button" wire:click="resetFilters" title="Reset filter" aria-label="Reset filter">
                        <x-ti-filter-x class="size-4" aria-hidden="true" />
                    </x-button.circle>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-select.styled
                    clearable
                    label="Bulan"
                    placeholder="Semua bulan"
                    wire:model.live="search_month"
                    :options="$this->monthOptions"
                    select="label:label|value:value"
                />
                <x-select.styled
                    clearable
                    label="Kategori"
                    placeholder="Semua kategori"
                    wire:model.live="search_category"
                    :options="\App\Enums\ExpenseCategoryEnum::choices()"
                    select="label:label|value:value"
                />
                <x-input
                    clearable
                    label="Keterangan"
                    placeholder="Cari keterangan..."
                    wire:model.live.debounce.400ms="search_description"
                />
            </div>
        </x-card>
    </div>

    <x-table :paginate="$this->dataTable">
        <x-table.thead class="bg-slate-100" :sortDirection="$sortDirection" :sortField="$sortField">
            <x-table.th label="No" width="5%" />
            <x-table.th label="Tanggal" sort="expense_date" />
            <x-table.th label="Kategori" sort="category" />
            <x-table.th label="Keterangan" />
            <x-table.th label="Dicatat Oleh" />
            <x-table.th label="Nominal" sort="amount" />
            <x-table.th label="Aksi" />
        </x-table.thead>

        <tbody>
            @forelse ($this->dataTable as $index => $d)
                <tr class="bg-white hover:bg-slate-50 transition-colors" wire:key="exp-{{ $d->id }}">
                    <td class="p-3 text-center text-slate-600 text-sm">
                        {{ $perPage * ($this->dataTable->currentPage() - 1) + $index + 1 }}
                    </td>
                    <td class="p-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $d->expense_date->format('d/m/Y') }}
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <x-badge :text="$d->category->label()" color="slate" light sm />
                    </td>
                    <td class="p-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $d->description }}</div>
                        @if ($d->notes)
                            <div class="text-xs text-slate-500">{{ $d->notes }}</div>
                        @endif
                    </td>
                    <td class="p-3 text-sm text-slate-700">{{ $d->user?->name ?? '-' }}</td>
                    <td class="p-3 text-sm font-bold text-rose-700 tabular-nums whitespace-nowrap">
                        {{ number_format($d->amount, 0, ',', '.') }}
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <x-button.circle
                                color="emerald"
                                sm
                                type="button"
                                wire:click="openEditModal({{ $d->id }})"
                                title="Ubah"
                                aria-label="Ubah {{ $d->description }}"
                            >
                                <x-ti-edit class="size-4" aria-hidden="true" />
                            </x-button.circle>

                            <x-button.circle
                                color="red"
                                flat
                                sm
                                type="button"
                                wire:click="$js.confirmDelete({{ $d->id }}, '{{ e($d->description) }}')"
                                title="Hapus"
                                aria-label="Hapus {{ $d->description }}"
                            >
                                <x-ti-trash class="size-4" aria-hidden="true" />
                            </x-button.circle>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="p-6 text-center text-sm text-slate-500" colspan="7">
                        Belum ada catatan pengeluaran kantor untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Catat / Ubah Pengeluaran --}}
    <x-modal wire="expenseModal" :title="$editingExpenseId ? 'Ubah Pengeluaran Kantor' : 'Catat Pengeluaran Kantor'" size="md" center="sm">
        <form wire:submit="saveExpense" class="space-y-4">
            <x-date label="Tanggal Pengeluaran" wire:model="expense_date" />

            <x-select.styled
                label="Kategori Pengeluaran"
                wire:model="category"
                :options="\App\Enums\ExpenseCategoryEnum::choices()"
                select="label:label|value:value"
            />

            <x-currency
                label="Nominal"
                wire:model="amount"
                locale="id-ID"
                :decimals="0"
                :precision="0"
            />

            <x-input
                label="Keterangan"
                placeholder="Misal: Beli deterjen cair 10 liter"
                wire:model="description"
            />

            <x-textarea
                label="Catatan Tambahan (Opsional)"
                placeholder="Nomor kwitansi atau detail toko"
                wire:model="notes"
                rows="2"
            />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('expenseModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="saveExpense">
                    Simpan Pengeluaran
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
