<div class="space-y-6">
    {{-- Header & Aksi Non-Cetak --}}
    <div class="no-print flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Nota {{ $transaction->invoice_number }}</h1>
            <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    <x-ti-home class="size-4" aria-hidden="true" />
                </a>
                <span>/</span>
                <a href="{{ route('transactions.index') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                    Transaksi
                </a>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ $transaction->invoice_number }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-button
                color="slate"
                outline
                sm
                href="{{ route('transactions.index') }}"
                wire:navigate
                title="Kembali"
                aria-label="Kembali"
            >
                <x-ti-arrow-left class="size-4 sm:mr-1 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Kembali</span>
            </x-button>
            <x-button
                color="emerald"
                sm
                type="button"
                onclick="window.print()"
                title="Cetak Nota"
                aria-label="Cetak Nota"
            >
                <x-ti-printer class="size-4 sm:mr-1.5 inline" aria-hidden="true" />
                <span class="hidden sm:inline">Cetak Nota</span>
            </x-button>
        </div>
    </div>

    {{-- Kontrol Status (Tidak Ikut Tercetak) --}}
    <x-card class="no-print bg-white border border-slate-200/80 shadow-xs">
        <form wire:submit="updateStatus" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <x-select.styled
                label="Status Cucian"
                wire:model="laundry_status"
                :options="\App\Enums\LaundryStatusEnum::choices()"
                select="label:label|value:value"
            />
            <x-select.styled
                label="Status Pembayaran"
                wire:model="payment_status"
                :options="\App\Enums\PaymentStatusEnum::choices()"
                select="label:label|value:value"
            />
            <div>
                <x-button submit color="emerald" loading="updateStatus" class="w-full justify-center">
                    Perbarui Status
                </x-button>
            </div>
        </form>
    </x-card>

    {{-- Lembar Nota Siap Cetak --}}
    <x-card class="bg-white border border-slate-200/80 shadow-xs max-w-2xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-5 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200">
                        <x-ti-wash-machine class="size-5" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">LondriKita</h2>
                        <p class="text-xs text-slate-500">Nota Layanan Laundry</p>
                    </div>
                </div>
            </div>

            <div class="sm:text-right text-sm">
                <p class="font-bold text-slate-900">{{ $transaction->invoice_number }}</p>
                <p class="text-xs text-slate-500">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-slate-500">Kasir: {{ $transaction->user?->name ?? '-' }}</p>
            </div>
        </div>

        {{-- Info Pelanggan & Status --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-4 border-b border-slate-200 text-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pelanggan</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $transaction->customer?->name ?? '-' }}</p>
                <p class="text-xs text-slate-600">{{ $transaction->customer?->phone ?? '-' }}</p>
                @if ($transaction->customer?->address)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $transaction->customer->address }}</p>
                @endif
            </div>
            <div class="sm:text-right space-y-1.5">
                <div>
                    <span class="text-xs text-slate-500 mr-1.5">Status Cucian:</span>
                    <x-badge :text="$transaction->laundry_status->label()" :color="$transaction->laundry_status->color()" light sm />
                </div>
                <div>
                    <span class="text-xs text-slate-500 mr-1.5">Pembayaran:</span>
                    <x-badge :text="$transaction->payment_status->label() . ' (' . $transaction->payment_method->label() . ')'" :color="$transaction->payment_status->color()" light sm />
                </div>
            </div>
        </div>

        {{-- Rincian Item Layanan --}}
        <div class="py-4 border-b border-slate-200 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="pb-2">Layanan</th>
                        <th class="pb-2 text-right">Volume</th>
                        <th class="pb-2 text-right">Tarif</th>
                        <th class="pb-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td class="py-2.5">
                                <div class="font-medium text-slate-900">{{ $item->service_name }}</div>
                                <div class="text-xs text-slate-500">{{ $item->category_name }}</div>
                            </td>
                            <td class="py-2.5 text-right tabular-nums text-slate-700">
                                {{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit->shortLabel() }}
                            </td>
                            <td class="py-2.5 text-right tabular-nums text-slate-700">
                                {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 text-right font-semibold text-slate-900 tabular-nums">
                                {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Rincian Biaya & Grand Total --}}
        <div class="py-4 space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal Layanan</span>
                <span class="tabular-nums text-slate-900">{{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>

            @if ($transaction->discount_amount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>
                        Diskon
                        @if ($transaction->discount_type === \App\Enums\DiscountTypeEnum::PERCENT)
                            ({{ $transaction->discount_value }}%)
                        @endif
                    </span>
                    <span class="tabular-nums">- {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($transaction->extra_water_fee > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Tambahan Air</span>
                    <span class="tabular-nums text-slate-900">{{ number_format($transaction->extra_water_fee, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($transaction->assistant_fee > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Asisten</span>
                    <span class="tabular-nums text-slate-900">{{ number_format($transaction->assistant_fee, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                <span class="text-base font-bold text-slate-900">Grand Total</span>
                <span class="text-xl font-bold text-emerald-700 tabular-nums">
                    {{ number_format($transaction->grand_total, 0, ',', '.') }}
                </span>
            </div>
        </div>

        @if ($transaction->notes)
            <div class="mt-2 rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2.5 text-xs text-slate-700">
                <span class="font-semibold text-slate-900">Catatan:</span> {{ $transaction->notes }}
            </div>
        @endif

        {{-- Informasi Poin Loyalitas --}}
        <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
            <span class="text-emerald-900 font-medium">
                Poin didapat dari transaksi ini: <strong>+{{ $transaction->points_earned }} Poin</strong>
            </span>
            <span class="text-emerald-800">
                Total poin pelanggan saat ini: <strong>{{ number_format($transaction->customer?->points ?? 0, 0, ',', '.') }} Poin</strong>
            </span>
        </div>

        <p class="mt-5 text-center text-xs text-slate-500">
            Terima kasih telah mempercayakan cucian Anda kepada LondriKita.
        </p>
    </x-card>
</div>
