<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">
                Halo, {{ auth()->user()?->name }}
            </h1>
            <p class="mt-0.5 text-sm text-slate-600">
                Ringkasan operasional meja kasir dan antrean cucian hari ini ({{ now()->translatedFormat('d M Y') }}).
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-button
                color="emerald"
                href="{{ route('pos') }}"
                wire:navigate
                title="Buat Transaksi Baru"
                aria-label="Buat Transaksi Baru"
            >
                <x-ti-plus class="size-4 sm:mr-1.5 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Buat Transaksi Baru</span>
            </x-button>
        </div>
    </div>

    {{-- Ringkasan Metrik Harian --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-card class="bg-emerald-50/70 border border-emerald-200 shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Omzet Hari Ini</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-950 tabular-nums">
                        {{ number_format($this->summary['today_revenue'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-emerald-800">
                        {{ $this->summary['today_count'] }} transaksi tercatat
                    </p>
                </div>
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200">
                    <x-ti-cash class="size-5" aria-hidden="true" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-sky-50/70 border border-sky-200 shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-800">Sedang Diproses</p>
                    <p class="mt-2 text-2xl font-bold text-sky-950 tabular-nums">
                        {{ $this->summary['in_progress_count'] }}
                    </p>
                    <p class="mt-1 text-xs text-sky-800">Diterima, dicuci, atau disetrika</p>
                </div>
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 border border-sky-200">
                    <x-ti-wash-machine class="size-5" aria-hidden="true" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-violet-50/70 border border-violet-200 shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-violet-800">Siap Diambil</p>
                    <p class="mt-2 text-2xl font-bold text-violet-950 tabular-nums">
                        {{ $this->summary['ready_count'] }}
                    </p>
                    <p class="mt-1 text-xs text-violet-800">Menunggu diambil pelanggan</p>
                </div>
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 border border-violet-200">
                    <x-ti-package class="size-5" aria-hidden="true" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pelanggan</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums">
                        {{ $this->summary['total_customers'] }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Terdaftar di program poin</p>
                </div>
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                    <x-ti-users class="size-5" aria-hidden="true" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Transaksi Terbaru --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs" paddingless>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200/80">
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Transaksi Terbaru</h2>
                <p class="text-xs text-slate-500">Ketuk nomor nota untuk melihat rincian atau mencetak struk.</p>
            </div>
            <a href="{{ route('transactions.index') }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                Lihat Semua
            </a>
        </div>

        @if ($this->recentTransactions->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm font-medium text-slate-700">Belum ada transaksi laundry yang tercatat.</p>
                <p class="mt-1 text-xs text-slate-500">Mulai catat cucian pelanggan melalui menu Kasir.</p>
                <div class="mt-4">
                    <x-button color="emerald" sm href="{{ route('pos') }}" wire:navigate>
                        <x-ti-receipt-2 class="size-4 mr-1 inline" aria-hidden="true" />
                        Buka Kasir
                    </x-button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">No. Nota</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Layanan</th>
                            <th class="px-5 py-3">Status Cucian</th>
                            <th class="px-5 py-3">Pembayaran</th>
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach ($this->recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition-colors" wire:key="home-tx-{{ $tx->id }}">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <a href="{{ route('transactions.show', $tx) }}" wire:navigate class="font-semibold text-emerald-700 hover:text-emerald-800">
                                        {{ $tx->invoice_number }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-slate-900">{{ $tx->customer?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $tx->customer?->phone ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-700">
                                    {{ $tx->items->pluck('service_name')->implode(', ') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge :text="$tx->laundry_status->label()" :color="$tx->laundry_status->color()" light sm />
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge :text="$tx->payment_status->label()" :color="$tx->payment_status->color()" light sm />
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-900 tabular-nums whitespace-nowrap">
                                    {{ number_format($tx->grand_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    @if (auth()->user()?->isAdmin())
        <x-card class="bg-white border border-slate-200/80 shadow-xs" paddingless>
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200/80">
                <div>
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900">Audit Perubahan Harga Terbaru</h2>
                    <p class="text-xs text-slate-500">Catatan penyesuaian tarif layanan oleh Pemilik (Admin).</p>
                </div>
                <a href="{{ route('catalog.index') }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                    Kelola Tarif
                </a>
            </div>

            @if ($this->recentPriceHistories->isEmpty())
                <div class="p-6 text-center text-sm text-slate-500">
                    Belum ada riwayat perubahan harga layanan.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($this->recentPriceHistories as $log)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-5 py-3.5 text-sm" wire:key="home-price-{{ $log->id }}">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $log->service_name }}
                                    <span class="font-normal text-slate-500">({{ $log->category_name }})</span>
                                </p>
                                <p class="text-xs text-slate-500">
                                    Diubah oleh {{ $log->user?->name ?? '-' }} pada {{ $log->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-xs sm:text-sm tabular-nums">
                                <span class="text-slate-500 line-through">{{ number_format($log->old_price, 0, ',', '.') }}</span>
                                <x-ti-arrow-right class="size-3.5 text-slate-400" aria-hidden="true" />
                                <span class="font-bold text-emerald-700">{{ number_format($log->new_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    @endif
</div>
