<div class="space-y-6">
    <div>
        <h1 class="text-lg sm:text-xl font-bold text-slate-900">Profil &amp; Akun</h1>
        <nav class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
            <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors" wire:navigate>
                <x-ti-home class="size-4" aria-hidden="true" />
            </a>
            <span>/</span>
            <span class="text-slate-700 font-medium">Profil &amp; Akun</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card class="bg-white border border-slate-200/80 shadow-xs">
            <div class="mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Informasi Akun</h2>
                <p class="text-xs text-slate-500">Peran saat ini: <strong>{{ auth()->user()?->isAdmin() ? 'Pemilik (Admin)' : 'Kasir' }}</strong></p>
            </div>

            <form wire:submit="updateProfile" class="space-y-4">
                <x-input label="Nama Lengkap" wire:model="name" />
                <x-input type="email" label="Alamat Email" wire:model="email" />
                <x-input label="Nomor Telepon" wire:model="phone" />

                <div class="pt-2">
                    <x-button submit color="emerald" loading="updateProfile">
                        Simpan Profil
                    </x-button>
                </div>
            </form>
        </x-card>

        <x-card class="bg-white border border-slate-200/80 shadow-xs">
            <div class="mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Ubah Kata Sandi</h2>
                <p class="text-xs text-slate-500">Gunakan kata sandi yang kuat untuk keamanan akun.</p>
            </div>

            <form wire:submit="updatePassword" class="space-y-4">
                <x-password label="Kata Sandi Saat Ini" wire:model="current_password" />
                <x-password label="Kata Sandi Baru" wire:model="password" />
                <x-password label="Konfirmasi Kata Sandi Baru" wire:model="password_confirmation" />

                <div class="pt-2">
                    <x-button submit color="emerald" loading="updatePassword">
                        Perbarui Kata Sandi
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
