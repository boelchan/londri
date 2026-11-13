<div class="space-y-6">
    <div class="flex justify-between items-center gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Rekap Transaksi &amp; Keuangan</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Rekap</span>
            </nav>
        </div>

        @if (auth()->user()?->isAdmin())
            <x-button
                color="emerald"
                outline
                sm
                href="{{ route('expenses.index') }}"
                wire:navigate
                title="Kelola Pengeluaran Kantor"
                aria-label="Kelola Pengeluaran Kantor"
            >
                <x-ti-receipt-off class="size-4 sm:mr-1.5 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Kelola Pengeluaran Kantor</span>
            </x-button>
        @endif
    </div>

    {{-- Filter Rekap --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs">
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-800 text-sm">
                    {{ auth()->user()?->isAdmin() ? 'Filter Periode & Kasir' : 'Filter Periode Transaksi Saya' }}
                </span>
                <x-button.circle color="red" flat sm type="button" wire:click="resetRecapFilters" title="Reset filter" aria-label="Reset filter">
                    <x-ti-filter-x class="size-4" aria-hidden="true" />
                </x-button.circle>
            </div>
            <span class="text-xs text-slate-500">Isi rentang tanggal jika ingin memfilter per tanggal spesifik.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()?->isAdmin() ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4">
            <x-select.styled
                clearable
                label="Per Bulan"
                placeholder="Semua bulan"
                wire:model.live="filter_month"
                :options="$this->monthOptions"
                select="label:label|value:value"
            />

            <x-date
                label="Dari Tanggal"
                placeholder="Pilih tanggal mulai"
                wire:model.live="filter_date_from"
            />

            <x-date
                label="Sampai Tanggal"
                placeholder="Pilih tanggal akhir"
                wire:model.live="filter_date_to"
            />

            @if (auth()->user()?->isAdmin())
                <x-select.styled
                    clearable
                    label="Per Kasir"
                    placeholder="Semua kasir"
                    wire:model.live="filter_cashier_id"
                    :options="$this->cashierOptions"
                    select="label:label|value:value"
                />
            @endif

            <x-select.styled
                clearable
                label="Status Pembayaran"
                placeholder="Semua status"
                wire:model.live="filter_payment_status"
                :options="\App\Enums\PaymentStatusEnum::choices()"
                select="label:label|value:value"
            />
        </div>
    </x-card>

    {{-- Ringkasan Uang Masuk & Pengeluaran --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()?->isAdmin() ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} gap-4">
        <x-card class="bg-emerald-50/70 border border-emerald-200 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Uang Masuk (Lunas)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-950 tabular-nums">
                {{ number_format($this->summary['paid_total'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-emerald-800">
                Dari {{ $this->summary['trx_count'] }} transaksi sesuai filter
            </p>
        </x-card>

        <x-card class="bg-amber-50/70 border border-amber-200 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-800">Belum Bayar (Piutang)</p>
            <p class="mt-2 text-2xl font-bold text-amber-950 tabular-nums">
                {{ number_format($this->summary['unpaid_total'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-amber-800">
                Total omzet nota: {{ number_format($this->summary['gross_total'], 0, ',', '.') }}
            </p>
        </x-card>

        @if (auth()->user()?->isAdmin())
            <x-card class="bg-rose-50/70 border border-rose-200 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-800">Pengeluaran Kantor</p>
                <p class="mt-2 text-2xl font-bold text-rose-950 tabular-nums">
                    {{ number_format($this->summary['expense_total'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-rose-800">Periode tanggal/bulan yang sama</p>
            </x-card>

            <x-card class="bg-white border border-slate-200/80 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kas Bersih</p>
                <p class="mt-2 text-2xl font-bold tabular-nums {{ $this->summary['net_cash'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                    {{ number_format($this->summary['net_cash'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Uang Masuk (Lunas) dikurangi Pengeluaran</p>
            </x-card>
        @endif
    </div>

    {{-- Rekap per Kasir (Khusus Pemilik) --}}
    @if (auth()->user()?->isAdmin())
        <x-card class="bg-white border border-slate-200/80 shadow-xs" paddingless>
        <div class="px-5 py-4 border-b border-slate-200/80">
            <h2 class="text-sm sm:text-base font-semibold text-slate-900">Rekap Uang Masuk per Kasir</h2>
            <p class="text-xs text-slate-500">Rincian transaksi dan uang masuk berdasarkan kasir pemroses pada filter aktif.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3">Nama Kasir / Pemroses</th>
                        <th class="px-5 py-3 text-right">Jumlah Trx</th>
                        <th class="px-5 py-3 text-right">Uang Masuk (Lunas)</th>
                        <th class="px-5 py-3 text-right">Belum Bayar</th>
                        <th class="px-5 py-3 text-right">Total Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($this->cashierRecap as $row)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="cashier-recap-{{ $row->user_id }}">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $row->cashier_name }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-slate-700">{{ $row->trx_count }} nota</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-700 tabular-nums">
                                {{ number_format($row->paid_total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right text-amber-700 tabular-nums">
                                {{ number_format($row->unpaid_total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 tabular-nums">
                                {{ number_format($row->gross_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-6 text-center text-sm text-slate-500" colspan="5">
                                Tidak ada transaksi kasir pada filter periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

    {{-- Daftar Transaksi Sesuai Filter --}}
    <x-table :paginate="$this->dataTable">
        <x-table.thead class="bg-slate-100" :sortDirection="$sortDirection" :sortField="$sortField">
            <x-table.th label="No" width="5%" />
            <x-table.th label="No. Nota" sort="invoice_number" />
            <x-table.th label="Tanggal" sort="created_at" />
            <x-table.th label="Kasir" />
            <x-table.th label="Pelanggan" />
            <x-table.th label="Pembayaran" sort="payment_status" />
            <x-table.th label="Total" sort="grand_total" />
        </x-table.thead>

        <tbody>
            @forelse ($this->dataTable as $index => $d)
                <tr class="bg-white hover:bg-slate-50 transition-colors" wire:key="recap-tx-{{ $d->id }}">
                    <td class="p-3 text-center text-slate-600 text-sm">
                        {{ $perPage * ($this->dataTable->currentPage() - 1) + $index + 1 }}
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        <a href="{{ route('transactions.show', $d) }}" wire:navigate class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            {{ $d->invoice_number }}
                        </a>
                    </td>
                    <td class="p-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $d->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="p-3 text-sm font-medium text-slate-800">{{ $d->user?->name ?? '-' }}</td>
                    <td class="p-3 text-sm text-slate-700">{{ $d->customer?->name ?? '-' }}</td>
                    <td class="p-3 whitespace-nowrap">
                        <x-badge :text="$d->payment_status->label()" :color="$d->payment_status->color()" light sm />
                        <span class="ml-1 text-xs text-slate-500">{{ $d->payment_method->label() }}</span>
                    </td>
                    <td class="p-3 text-sm font-bold text-slate-900 tabular-nums whitespace-nowrap">
                        {{ number_format($d->grand_total, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="p-6 text-center text-sm text-slate-500" colspan="7">
                        Tidak ada data transaksi yang cocok dengan filter rekap.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>
