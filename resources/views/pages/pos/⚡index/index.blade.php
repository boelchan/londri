<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Kasir Transaksi Laundry</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Kasir</span>
            </nav>
        </div>

        <x-button
            color="slate"
            outline
            sm
            href="{{ route('transactions.index') }}"
            wire:navigate
            title="Lihat Daftar Nota"
            aria-label="Lihat Daftar Nota"
        >
            <x-ti-list-details class="size-4 sm:mr-1.5 inline" aria-hidden="true" />
            <span class="hidden sm:inline">Lihat Daftar Nota</span>
        </x-button>
    </div>

    <form wire:submit="submitTransaction" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Kolom Kiri: Pelanggan, Layanan, Biaya Tambahan --}}
        <div class="lg:col-span-7 space-y-5">
            {{-- 1. Master Pelanggan --}}
            <x-card class="bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900">1. Data Pelanggan</h2>
                    <x-button
                        type="button"
                        color="emerald"
                        outline
                        sm
                        wire:click="$set('customerModal', true)"
                        title="Pelanggan Baru"
                        aria-label="Pelanggan Baru"
                    >
                        <x-ti-user-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
                        <span class="hidden sm:inline">Pelanggan Baru</span>
                    </x-button>
                </div>

                <x-select.styled
                    label="Pelanggan"
                    placeholder="Cari nama pelanggan..."
                    searchable
                    wire:model.live="customer_id"
                    :options="$this->customerOptions"
                    select="label:label|value:value"
                />

                @if ($this->selectedCustomer)
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3.5 py-2.5 text-xs">
                        <div class="text-slate-700">
                            <span class="font-semibold text-slate-900">{{ $this->selectedCustomer->name }}</span>
                            <span class="mx-1 text-slate-400">&bull;</span>
                            <span>{{ $this->selectedCustomer->phone }}</span>
                            @if ($this->selectedCustomer->address)
                                <p class="mt-0.5 text-slate-600">{{ $this->selectedCustomer->address }}</p>
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 border border-emerald-200 font-semibold text-emerald-800">
                            <x-ti-coins class="size-3.5 text-emerald-600" aria-hidden="true" />
                            <span>{{ number_format($this->selectedCustomer->points, 0, ',', '.') }} Poin</span>
                        </div>
                    </div>
                @endif
            </x-card>

            {{-- 2. Pemilihan Kategori, Layanan & Volume --}}
            <x-card class="bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900">2. Kategori, Layanan &amp; Volume</h2>
                    <x-button
                        type="button"
                        color="emerald"
                        outline
                        sm
                        wire:click="addRow"
                        title="Tambah Layanan"
                        aria-label="Tambah Layanan"
                    >
                        <x-ti-plus class="size-4 sm:mr-1 inline" aria-hidden="true" />
                        <span class="hidden sm:inline">Tambah Layanan</span>
                    </x-button>
                </div>

                <div class="space-y-4">
                    @foreach ($rows as $index => $row)
                        @php
                            $selectedService = ! empty($row['service_id'])
                                ? $this->activeServices->firstWhere('id', (int) $row['service_id'])
                                : null;
                            $rowQty = max(0.0, (float) ($row['quantity'] ?? 0));
                            $rowSubtotal = $selectedService ? (int) round($selectedService->price * $rowQty) : 0;
                        @endphp

                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-3" wire:key="pos-row-{{ $index }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Item #{{ $index + 1 }}
                                </span>
                                @if (count($rows) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeRow({{ $index }})"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:text-rose-700"
                                    >
                                        <x-ti-trash class="size-3.5" aria-hidden="true" />
                                        Hapus
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-4">
                                    <x-select.styled
                                        label="Kategori"
                                        placeholder="Pilih kategori"
                                        wire:model.live="rows.{{ $index }}.category_id"
                                        :options="$this->categoryOptions"
                                        select="label:label|value:value"
                                    />
                                </div>

                                <div class="sm:col-span-5">
                                    <x-select.styled
                                        label="Jenis Layanan"
                                        placeholder="Pilih layanan"
                                        searchable
                                        wire:model.live="rows.{{ $index }}.service_id"
                                        :options="$this->serviceOptionsForCategory($row['category_id'] ? (int) $row['category_id'] : null)"
                                        select="label:label|value:value"
                                    />
                                </div>

                                <div class="sm:col-span-3">
                                    <x-number
                                        label="Volume ({{ $selectedService?->unit->shortLabel() ?? 'kg/qty' }})"
                                        wire:model.live.debounce.250ms="rows.{{ $index }}.quantity"
                                        :min="0.1"
                                        :step="$selectedService?->unit->value === 'kg' ? 0.5 : 1"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/70 text-xs">
                                <span class="text-slate-600">
                                    @if ($selectedService)
                                        Tarif aktif: <strong class="text-slate-800">{{ number_format($selectedService->price, 0, ',', '.') }}</strong> / {{ $selectedService->unit->shortLabel() }}
                                        @if ($selectedService->estimated_duration)
                                            &bull; Estimasi {{ $selectedService->estimated_duration }}
                                        @endif
                                    @else
                                        Pilih jenis layanan untuk melihat tarif aktif.
                                    @endif
                                </span>
                                <span class="font-semibold text-slate-900 tabular-nums">
                                    Subtotal: {{ number_format($rowSubtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            {{-- 3. Diskon & Komponen Biaya Tambahan --}}
            <x-card class="bg-white border border-slate-200/80 shadow-xs">
                <div class="mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900">3. Diskon &amp; Biaya Tambahan</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-select.styled
                        label="Tipe Diskon"
                        wire:model.live="discount_type"
                        :options="\App\Enums\DiscountTypeEnum::choices()"
                        select="label:label|value:value"
                    />

                    @if ($discount_type === \App\Enums\DiscountTypeEnum::PERCENT->value)
                        <x-number
                            label="Persentase Diskon (%)"
                            wire:model.live.debounce.250ms="discount_value"
                            :min="0"
                            :max="100"
                            :step="1"
                        />
                    @elseif ($discount_type === \App\Enums\DiscountTypeEnum::NOMINAL->value)
                        <x-currency
                            label="Nominal Diskon"
                            wire:model.live.debounce.250ms="discount_value"
                            locale="id-ID"
                            :decimals="0"
                            :precision="0"
                        />
                    @else
                        <x-input
                            label="Nilai Diskon"
                            value="0"
                            disabled
                        />
                    @endif

                    <x-currency
                        label="Biaya Tambahan Air (Opsional)"
                        wire:model.live.debounce.250ms="extra_water_fee"
                        locale="id-ID"
                        :decimals="0"
                        :precision="0"
                    />

                    <x-currency
                        label="Biaya Asisten (Opsional)"
                        wire:model.live.debounce.250ms="assistant_fee"
                        locale="id-ID"
                        :decimals="0"
                        :precision="0"
                    />
                </div>

                <div class="mt-4">
                    <x-textarea
                        label="Catatan Transaksi (Opsional)"
                        placeholder="Misal: pisahkan baju putih, jangan pakai pewangi..."
                        wire:model="notes"
                        rows="2"
                    />
                </div>
            </x-card>
        </div>

        {{-- Kolom Kanan: Ringkasan Nota & Finalisasi --}}
        <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-5">
            <x-card class="bg-white border border-slate-200/80 shadow-xs">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900 pb-3 border-b border-slate-200/80">
                    Ringkasan Nota
                </h2>

                <div class="mt-4 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal Layanan</span>
                        <span class="font-medium text-slate-900 tabular-nums">
                            {{ number_format($this->summary['subtotal'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Diskon</span>
                        <span class="font-medium text-rose-600 tabular-nums">
                            - {{ number_format($this->summary['discount_amount'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Biaya Tambahan Air</span>
                        <span class="font-medium text-slate-900 tabular-nums">
                            {{ number_format($this->summary['extra_water_fee'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Biaya Asisten</span>
                        <span class="font-medium text-slate-900 tabular-nums">
                            {{ number_format($this->summary['assistant_fee'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-base font-bold text-slate-900">Grand Total</span>
                        <span class="text-2xl font-bold text-emerald-700 tabular-nums">
                            {{ number_format($this->summary['grand_total'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 flex items-center justify-between text-xs">
                        <span class="font-medium text-emerald-900">Estimasi Poin Loyalitas</span>
                        <span class="font-bold text-emerald-800 tabular-nums">
                            +{{ $this->summary['points_earned'] }} Poin
                        </span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-200/80 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-select.styled
                            label="Status Pembayaran"
                            wire:model="payment_status"
                            :options="\App\Enums\PaymentStatusEnum::choices()"
                            select="label:label|value:value"
                        />

                        <x-select.styled
                            label="Metode Pembayaran"
                            wire:model="payment_method"
                            :options="\App\Enums\PaymentMethodEnum::choices()"
                            select="label:label|value:value"
                        />
                    </div>

                    <x-button
                        submit
                        color="emerald"
                        loading="submitTransaction"
                        class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
                    >
                        <x-ti-printer class="size-4 mr-1.5" aria-hidden="true" />
                        Simpan &amp; Proses Nota
                    </x-button>
                </div>
            </x-card>
        </div>
    </form>

    {{-- Modal Tambah Pelanggan Cepat --}}
    <x-modal wire="customerModal" title="Tambah Pelanggan Baru" size="md" center="sm">
        <form wire:submit="saveQuickCustomer" class="space-y-4">
            <x-input
                label="Nama Lengkap"
                placeholder="Contoh: Ibu Sari Wijaya"
                wire:model="new_customer_name"
            />

            <x-input
                label="Nomor Telepon / WhatsApp"
                placeholder="Contoh: 081234567890"
                wire:model="new_customer_phone"
            />

            <x-textarea
                label="Alamat (Opsional)"
                placeholder="Alamat lengkap pelanggan"
                wire:model="new_customer_address"
                rows="2"
            />

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" color="slate" outline wire:click="$set('customerModal', false)">
                    Batal
                </x-button>
                <x-button submit color="emerald" loading="saveQuickCustomer">
                    Simpan Pelanggan
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Rekap Per Kasir Hari Ini --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs" paddingless>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-slate-200/80">
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">
                    Rekap Kasir Hari Ini: {{ $this->cashierTodaySummary['cashier_name'] }}
                </h2>
                <p class="text-xs text-slate-500">
                    Ringkasan nota dan uang masuk hari ini ({{ now()->translatedFormat('d M Y') }}).
                </p>
            </div>

            @if (auth()->user()?->isAdmin())
                <div class="w-full sm:w-56">
                    <x-select.styled
                        placeholder="Pilih kasir"
                        wire:model.live="recap_cashier_id"
                        :options="$this->cashierOptions"
                        select="label:label|value:value"
                    />
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 p-5 border-b border-slate-200/80 bg-slate-50/60">
            <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                <p class="text-xs font-medium text-slate-500">Jumlah Nota</p>
                <p class="mt-1 text-lg font-bold text-slate-900 tabular-nums">{{ $this->cashierTodaySummary['trx_count'] }} nota</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5">
                <p class="text-xs font-medium text-emerald-800">Uang Masuk (Lunas)</p>
                <p class="mt-1 text-lg font-bold text-emerald-900 tabular-nums">
                    {{ number_format($this->cashierTodaySummary['paid_total'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                <p class="text-xs font-medium text-slate-500">Tunai / Non-Tunai</p>
                <p class="mt-1 text-xs font-semibold text-slate-800 tabular-nums">
                    Tunai: {{ number_format($this->cashierTodaySummary['cash_total'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-600 tabular-nums">
                    QRIS/TF: {{ number_format($this->cashierTodaySummary['non_cash_total'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5">
                <p class="text-xs font-medium text-amber-800">Belum Bayar</p>
                <p class="mt-1 text-lg font-bold text-amber-900 tabular-nums">
                    {{ number_format($this->cashierTodaySummary['unpaid_total'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        @if ($this->cashierTodayTransactions->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500">
                Belum ada transaksi oleh {{ $this->cashierTodaySummary['cashier_name'] }} hari ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-2.5">No. Nota</th>
                            <th class="px-5 py-2.5">Jam</th>
                            <th class="px-5 py-2.5">Pelanggan</th>
                            <th class="px-5 py-2.5">Pembayaran</th>
                            <th class="px-5 py-2.5 text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach ($this->cashierTodayTransactions as $tx)
                            <tr class="hover:bg-slate-50 transition-colors" wire:key="pos-recap-{{ $tx->id }}">
                                <td class="px-5 py-2.5 whitespace-nowrap">
                                    <a href="{{ route('transactions.show', $tx) }}" wire:navigate class="font-semibold text-emerald-700 hover:text-emerald-800">
                                        {{ $tx->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('H:i') }}</td>
                                <td class="px-5 py-2.5 text-slate-800">{{ $tx->customer?->name ?? '-' }}</td>
                                <td class="px-5 py-2.5 whitespace-nowrap">
                                    <x-badge :text="$tx->payment_status->label() . ' (' . $tx->payment_method->label() . ')'" :color="$tx->payment_status->color()" light xs />
                                </td>
                                <td class="px-5 py-2.5 text-right font-semibold text-slate-900 tabular-nums whitespace-nowrap">
                                    {{ number_format($tx->grand_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
