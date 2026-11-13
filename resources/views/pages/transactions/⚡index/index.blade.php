<div class="space-y-6">
    <div class="flex justify-between items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Riwayat Transaksi Laundry</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Riwayat Transaksi</span>
            </nav>
        </div>

        <x-button
            color="emerald"
            sm
            href="{{ route('pos') }}"
            wire:navigate
            title="Transaksi Baru"
            aria-label="Transaksi Baru"
        >
            <x-ti-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
            <span class="hidden sm:inline">Transaksi Baru</span>
        </x-button>
    </div>

    <x-card class="bg-white border border-slate-200/80 shadow-xs">
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-800 text-sm">Pencarian &amp; Filter</span>
                <x-button.circle color="red" flat sm type="button" wire:click="resetFilters" title="Reset filter" aria-label="Reset filter">
                    <x-ti-filter-x class="size-4" aria-hidden="true" />
                </x-button.circle>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-input clearable label="No. Nota" placeholder="Cari LDR-..." wire:model.live.debounce.400ms="search_invoice" />
            <x-input clearable label="Pelanggan" placeholder="Nama atau no. HP..." wire:model.live.debounce.400ms="search_customer" />
            <x-select.styled
                clearable
                label="Status Cucian"
                placeholder="Semua status"
                wire:model.live="search_laundry_status"
                :options="\App\Enums\LaundryStatusEnum::choices()"
                select="label:label|value:value"
            />
            <x-select.styled
                clearable
                label="Status Pembayaran"
                placeholder="Semua pembayaran"
                wire:model.live="search_payment_status"
                :options="\App\Enums\PaymentStatusEnum::choices()"
                select="label:label|value:value"
            />
        </div>
    </x-card>

    <x-table :paginate="$this->dataTable">
        <x-table.thead class="bg-slate-100" :sortDirection="$sortDirection" :sortField="$sortField">
            <x-table.th label="No" width="5%" />
            <x-table.th label="No. Nota" sort="invoice_number" />
            <x-table.th label="Pelanggan" />
            <x-table.th label="Layanan" />
            <x-table.th label="Status Cucian" sort="laundry_status" />
            <x-table.th label="Pembayaran" sort="payment_status" />
            <x-table.th label="Grand Total" sort="grand_total" />
            <x-table.th label="Aksi" />
        </x-table.thead>

        <tbody>
            @forelse ($this->dataTable as $index => $d)
                <tr class="bg-white hover:bg-slate-50 transition-colors" wire:key="tx-{{ $d->id }}">
                    <td class="p-3 text-center text-slate-600 text-sm">
                        {{ $perPage * ($this->dataTable->currentPage() - 1) + $index + 1 }}
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <a href="{{ route('transactions.show', $d) }}" wire:navigate class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            {{ $d->invoice_number }}
                        </a>
                        <div class="text-xs text-slate-500">{{ $d->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="p-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $d->customer?->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $d->customer?->phone ?? '-' }}</div>
                    </td>
                    <td class="p-3 text-sm text-slate-700">
                        @foreach ($d->items as $item)
                            <div class="text-xs">
                                {{ $item->service_name }} ({{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit->shortLabel() }})
                            </div>
                        @endforeach
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <x-badge :text="$d->laundry_status->label()" :color="$d->laundry_status->color()" light sm />
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <x-badge :text="$d->payment_status->label()" :color="$d->payment_status->color()" light sm />
                        <div class="mt-0.5 text-xs text-slate-500">{{ $d->payment_method->label() }}</div>
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <div class="text-sm font-bold text-slate-900 tabular-nums">
                            {{ number_format($d->grand_total, 0, ',', '.') }}
                        </div>
                        @if ($d->points_earned > 0)
                            <div class="text-xs text-emerald-700 font-medium">+{{ $d->points_earned }} poin</div>
                        @endif
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <x-button.circle
                                color="emerald"
                                sm
                                href="{{ route('transactions.show', $d) }}"
                                title="Detail & Cetak Nota"
                                aria-label="Detail & Cetak Nota {{ $d->invoice_number }}"
                                wire:navigate
                            >
                                <x-ti-printer class="size-4" aria-hidden="true" />
                            </x-button.circle>

                            @if ($d->payment_status === \App\Enums\PaymentStatusEnum::UNPAID)
                                <x-button.circle
                                    color="sky"
                                    outline
                                    sm
                                    type="button"
                                    wire:click="markAsPaid({{ $d->id }})"
                                    title="Tandai Lunas"
                                    aria-label="Tandai Lunas {{ $d->invoice_number }}"
                                >
                                    <x-ti-check class="size-4" aria-hidden="true" />
                                </x-button.circle>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="p-6 text-center text-sm text-slate-500" colspan="8">
                        Belum ada data transaksi laundry yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>
