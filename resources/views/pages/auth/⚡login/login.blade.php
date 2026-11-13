<div class="relative flex min-h-[calc(100dvh-8rem)] items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex items-center gap-3.5 px-1">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-100 text-emerald-700 shadow-xs">
                <x-ti-wash-machine class="size-6" aria-hidden="true" />
            </div>
            <div class="min-w-0">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                    LondriKita
                </h1>
                <p class="mt-0.5 text-sm text-slate-600">
                    Sistem Kasir &amp; Operasional Laundry
                </p>
            </div>
        </div>

        <x-card class="bg-white border border-slate-200/80 shadow-xs">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">Masuk ke Sistem</h2>
                <p class="mt-1 text-sm text-slate-600">Gunakan akun Kasir atau Pemilik (Admin).</p>
            </div>

            @if (session('error'))
                <div class="mb-4">
                    <x-alert color="red" light :text="session('error')" />
                </div>
            @endif

            <form class="space-y-4" wire:submit="authenticate">
                <x-input
                    type="email"
                    label="Alamat Email"
                    placeholder="kasir@londri.id"
                    wire:model="email"
                />

                <x-password
                    label="Kata Sandi"
                    placeholder="Masukkan kata sandi"
                    wire:model="password"
                />

                <x-checkbox
                    label="Ingat saya di perangkat ini"
                    wire:model="remember"
                    color="emerald"
                />

                <div class="pt-1">
                    <x-button
                        submit
                        color="emerald"
                        loading="authenticate"
                        class="w-full justify-center rounded-xl py-2.5 text-sm font-semibold cursor-pointer"
                    >
                        <x-ti-login class="mr-1.5 size-4" aria-hidden="true" />
                        <span>Masuk</span>
                    </x-button>
                </div>
            </form>
        </x-card>

        <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-500">
            <x-ti-shield-lock class="size-3.5 text-emerald-600" aria-hidden="true" />
            <span>Akses dibedakan otomatis sesuai peran Kasir atau Pemilik.</span>
        </p>
    </div>
</div>
