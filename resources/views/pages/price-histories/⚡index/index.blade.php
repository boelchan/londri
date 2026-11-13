<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Audit Trail Perubahan Harga</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span>Panel Pemilik</span>
                <span>/</span>
                <span class="text-slate-700 font-medium">Audit Harga</span>
            </nav>
        </div>

        <x-button color="emerald" outline sm href="{{ route('catalog.index') }}" wire:navigate>
            <x-ti-tags class="size-4 mr-1 inline" aria-hidden="true" />
            Kelola Tarif Layanan
        </x-button>
    </div>

    <x-card class="bg-white border border-slate-200/80 shadow-xs">
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-800 text-sm">Filter Riwayat Harga</span>
                <x-button.circle color="red" flat sm type="button" wire:click="resetFilters" title="Reset filter" aria-label="Reset filter">
                    <x-ti-filter-x class="size-4" aria-hidden="true" />
                </x-button.circle>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input clearable label="Nama Layanan" placeholder="Cari layanan..." wire:model.live.debounce.400ms="search_service" />
            <x-input clearable label="Kategori" placeholder="Cari kategori..." wire:model.live.debounce.400ms="search_category" />
        </div>
    </x-card>

    <x-table :paginate="$this->dataTable">
        <x-table.thead class="bg-slate-100" :sortDirection="$sortDirection" :sortField="$sortField">
            <x-table.th label="No" width="5%" />
            <x-table.th label="Waktu Perubahan" sort="created_at" />
            <x-table.th label="Kategori" sort="category_name" />
            <x-table.th label="Nama Layanan" sort="service_name" />
            <x-table.th label="Harga Lama" sort="old_price" />
            <x-table.th label="Harga Baru" sort="new_price" />
            <x-table.th label="Admin Pengubah" />
            <x-table.th label="Catatan" />
        </x-table.thead>

        <tbody>
            @forelse ($this->dataTable as $index => $d)
                @php
                    $diff = $d->new_price - $d->old_price;
                @endphp
                <tr class="bg-white hover:bg-slate-50 transition-colors" wire:key="ph-{{ $d->id }}">
                    <td class="p-3 text-center text-slate-600 text-sm">
                        {{ $perPage * ($this->dataTable->currentPage() - 1) + $index + 1 }}
                    </td>
                    <td class="p-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $d->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="p-3 text-sm text-slate-600">{{ $d->category_name }}</td>
                    <td class="p-3 text-sm font-semibold text-slate-900">{{ $d->service_name }}</td>
                    <td class="p-3 text-sm text-slate-500 line-through tabular-nums whitespace-nowrap">
                        Rp {{ number_format($d->old_price, 0, ',', '.') }}
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900 tabular-nums">
                            Rp {{ number_format($d->new_price, 0, ',', '.') }}
                        </span>
                        <span class="ml-1 text-xs font-semibold tabular-nums {{ $diff >= 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                            ({{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }})
                        </span>
                    </td>
                    <td class="p-3 text-sm text-slate-800">{{ $d->user?->name ?? '-' }}</td>
                    <td class="p-3 text-xs text-slate-600">{{ $d->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="p-6 text-center text-sm text-slate-500" colspan="8">
                        Belum ada catatan perubahan harga layanan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>
