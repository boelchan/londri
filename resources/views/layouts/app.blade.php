<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" rel="icon" sizes="96x96" />
    <link type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" rel="icon" />
    <link href="{{ asset('favicon/favicon.ico') }}" rel="shortcut icon" />
    <link href="{{ asset('favicon/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180" />
    <meta name="apple-mobile-web-app-title" content="LondriKita" />

    <title>{{ isset($title) ? $title . ' - LondriKita' : 'LondriKita - Sistem Penyelenggaraan Laundry' }}</title>

    <!-- Tipografi: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <tallstackui:script />
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-900 antialiased font-sans">
    <div class="no-print">
        <x-toast />
        <x-dialog />
    </div>

    <div
        class="relative z-10 min-h-screen w-full flex flex-col lg:flex-row"
        x-data="{
            sidebarOpen: localStorage.getItem('sidebar_expanded') !== 'false',
            mobileSidebarOpen: false,
            toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = !this.sidebarOpen;
                    localStorage.setItem('sidebar_expanded', this.sidebarOpen);
                } else {
                    this.mobileSidebarOpen = !this.mobileSidebarOpen;
                }
            },
            closeMobile() {
                this.mobileSidebarOpen = false;
            }
        }"
        x-on:keydown.escape.window="closeMobile()"
    >
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div
            class="no-print fixed inset-0 z-40 bg-slate-900/40 transition-opacity lg:hidden"
            x-show="mobileSidebarOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeMobile()"
            x-cloak
        ></div>

        @if (Auth::check())
            <aside
                id="sidebar"
                aria-label="Navigasi Utama"
                class="no-print fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-100 border-r border-slate-200 p-3 transition-all duration-300 ease-in-out lg:static lg:flex lg:h-screen lg:sticky lg:top-0 lg:z-30 select-none overflow-hidden"
                :class="[
                    mobileSidebarOpen ? 'translate-x-0 shadow-lg w-72 max-w-[85vw]' : '-translate-x-full lg:translate-x-0',
                    sidebarOpen ? 'lg:w-64 lg:p-3.5' : 'lg:w-18 lg:p-2.5'
                ]"
            >
                <div class="relative z-10 flex h-full flex-col min-h-0">
                    {{-- Header saat Sidebar Terbuka --}}
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200/80" x-show="sidebarOpen || window.innerWidth < 1024">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 min-w-0 rounded-xl p-1.5 hover:bg-white/80 transition-colors" wire:navigate @click="closeMobile()">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100 text-emerald-700 shadow-2xs">
                                <x-ti-wash-machine class="size-5" aria-hidden="true" />
                            </div>
                            <div class="leading-tight truncate">
                                <span class="block text-sm font-bold text-slate-900 truncate">LondriKita</span>
                                <span class="block text-xs text-slate-600 truncate">Kasir &amp; Manajemen</span>
                            </div>
                        </a>

                        <button
                            type="button"
                            @click="closeMobile()"
                            class="inline-flex size-8 shrink-0 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-200/70 hover:text-slate-900 transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600 lg:hidden"
                            title="Tutup Menu"
                            aria-label="Tutup Menu"
                        >
                            <x-ti-x class="size-4.5" aria-hidden="true" />
                        </button>
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="hidden lg:inline-flex size-8 shrink-0 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-200/70 hover:text-slate-900 transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600"
                            title="Perkecil Sidebar"
                            aria-label="Perkecil Sidebar"
                        >
                            <x-ti-layout-sidebar-left-collapse class="size-4.5" aria-hidden="true" />
                        </button>
                    </div>

                    {{-- Header saat Sidebar Diciutkan di Desktop --}}
                    <div class="hidden lg:flex flex-col items-center gap-2 pb-3 mb-3 border-b border-slate-200/80" x-show="!sidebarOpen" x-cloak>
                        <a
                            href="{{ route('home') }}"
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100 text-emerald-700 hover:bg-emerald-200/70 transition-colors"
                            wire:navigate
                            x-tooltip="LondriKita"
                            data-position="right"
                            data-tooltip-delay="flash"
                            x-bind:data-tooltip-disabled="sidebarOpen || mobileSidebarOpen"
                            aria-label="LondriKita"
                        >
                            <x-ti-wash-machine class="size-5" aria-hidden="true" />
                        </a>
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="inline-flex size-8 items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600"
                            x-tooltip="Perbesar Sidebar"
                            data-position="right"
                            data-tooltip-delay="flash"
                            x-bind:data-tooltip-disabled="sidebarOpen || mobileSidebarOpen"
                            aria-label="Perbesar Sidebar"
                        >
                            <x-ti-layout-sidebar-left-expand class="size-4.5" aria-hidden="true" />
                        </button>
                    </div>

                    <nav class="flex-1 overflow-y-auto space-y-4" aria-label="Menu Utama">
                        <ul class="flex flex-col gap-1 w-full ps-0">
                            <x-sidebar.item
                                href="{{ route('home') }}"
                                icon="home"
                                title="Beranda"
                                :active="request()->routeIs('home')"
                            />

                            <x-sidebar.item
                                href="{{ route('pos') }}"
                                icon="receipt-2"
                                title="Kasir (Transaksi)"
                                :active="request()->routeIs('pos')"
                            />

                            <x-sidebar.item
                                href="{{ route('transactions.index') }}"
                                icon="list-details"
                                title="Riwayat Transaksi"
                                :active="request()->routeIs('transactions.*')"
                            />

                            <x-sidebar.item
                                href="{{ route('customers.index') }}"
                                icon="users"
                                title="Pelanggan & Poin"
                                :active="request()->routeIs('customers.*')"
                            />

                            <x-sidebar.item
                                href="{{ route('recap.index') }}"
                                icon="report-analytics"
                                title="Rekap"
                                :active="request()->routeIs('recap.*')"
                            />
                        </ul>

                        @if (Auth::user()?->isAdmin())
                            <div>
                                <p class="px-3 mb-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">
                                    Panel Pemilik
                                </p>
                                <ul class="flex flex-col gap-1 w-full ps-0">
                                    <x-sidebar.item
                                        href="{{ route('expenses.index') }}"
                                        icon="receipt-off"
                                        title="Pengeluaran Kantor"
                                        :active="request()->routeIs('expenses.*')"
                                    />

                                    <x-sidebar.item
                                        href="{{ route('catalog.index') }}"
                                        icon="tags"
                                        title="Kategori & Layanan"
                                        :active="request()->routeIs('catalog.*')"
                                    />

                                    <x-sidebar.item
                                        href="{{ route('user.index') }}"
                                        icon="user-cog"
                                        title="Kelola Pengguna"
                                        :active="request()->routeIs('user.*')"
                                    />
                                </ul>
                            </div>
                        @endif
                    </nav>

                    {{-- Kartu Profil Pengguna & Tombol Keluar --}}
                    <div class="mt-auto pt-2 border-t border-slate-200/80">
                        <div class="relative w-full" x-data="{ sidebarUserMenuOpen: false }">
                            <button
                                type="button"
                                x-ref="userMenuTrigger"
                                class="flex w-full items-center rounded-xl p-2 bg-white hover:bg-emerald-50/40 border border-slate-200/90 shadow-2xs transition-all text-left focus-visible:outline-2 focus-visible:outline-emerald-600"
                                :class="sidebarOpen ? 'justify-between' : 'lg:justify-center'"
                                @click="sidebarUserMenuOpen = !sidebarUserMenuOpen"
                                x-tooltip="{{ e(Auth::user()?->name) }}"
                                data-position="right"
                                data-tooltip-delay="flash"
                                x-bind:data-tooltip-disabled="sidebarOpen || mobileSidebarOpen || sidebarUserMenuOpen"
                                aria-label="Menu akun {{ Auth::user()?->name }}"
                            >
                                <div class="flex items-center gap-2.5 min-w-0" :class="sidebarOpen ? '' : 'lg:justify-center'">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs uppercase">
                                        {{ substr(auth()->user()->name, 0, 2) }}
                                    </div>
                                    <div class="truncate leading-tight" :class="sidebarOpen ? 'block' : 'lg:hidden'">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()?->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()?->isAdmin() ? 'Pemilik (Admin)' : 'Kasir' }}</p>
                                    </div>
                                </div>
                                <div :class="sidebarOpen ? 'block' : 'lg:hidden'">
                                    <x-ti-selector class="size-4 text-slate-500 shrink-0" aria-hidden="true" />
                                </div>
                            </button>

                            <x-account-menu position="top-start" trigger="$refs.userMenuTrigger" state="sidebarUserMenuOpen" />
                        </div>
                    </div>
                </div>
            </aside>
        @endif

        <!-- Form logout tersembunyi -->
        <form class="hidden" id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
        </form>

        <div class="flex flex-1 flex-col min-w-0">
            @auth
                {{-- Header Mobile dengan tombol buka menu --}}
                <header class="no-print sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2.5 lg:hidden">
                    <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                        <div class="flex size-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-100 text-emerald-700">
                            <x-ti-wash-machine class="size-4.5" aria-hidden="true" />
                        </div>
                        <span class="text-sm font-bold text-slate-900">LondriKita</span>
                    </a>
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                        aria-label="Buka Menu"
                    >
                        <x-ti-menu-2 class="size-4" aria-hidden="true" />
                        <span>Menu</span>
                    </button>
                </header>
            @endauth

            <!-- Konten Utama -->
            <main id="main-content" class="w-full flex-1 overflow-y-auto flex flex-col min-h-screen lg:min-h-0 pb-20 lg:pb-0">
                <div class="flex-1 p-4 sm:p-6 lg:p-8 max-w-6xl w-full mx-auto">
                    {{ $slot }}
                </div>

                <footer class="no-print mt-auto border-t border-slate-200 bg-white py-3.5 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 select-none">
                    <p class="leading-relaxed">
                        &copy; {{ date('Y') }} <strong class="text-slate-700">LondriKita</strong> &bull; Sistem Penyelenggaraan Laundry
                    </p>
                </footer>
            </main>

            <!-- Bottom Navigation Khusus Mobile -->
            @auth
                <nav
                    aria-label="Navigasi Bawah Ponsel"
                    class="no-print fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white px-3 py-1.5 lg:hidden select-none shadow-sm"
                >
                    <div class="grid grid-cols-4 gap-1.5 max-w-md mx-auto">
                        <a
                            href="{{ route('home') }}"
                            wire:navigate
                            class="flex flex-col items-center justify-center gap-1 min-h-12 rounded-xl px-2 py-1.5 text-xs font-semibold transition-colors {{ request()->routeIs('home') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <x-ti-home class="size-5 {{ request()->routeIs('home') ? 'text-emerald-700' : 'text-slate-500' }}" aria-hidden="true" />
                            <span>Beranda</span>
                        </a>

                        <a
                            href="{{ route('pos') }}"
                            wire:navigate
                            class="flex flex-col items-center justify-center gap-1 min-h-12 rounded-xl px-2 py-1.5 text-xs font-semibold transition-colors {{ request()->routeIs('pos') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <x-ti-receipt-2 class="size-5 {{ request()->routeIs('pos') ? 'text-emerald-700' : 'text-slate-500' }}" aria-hidden="true" />
                            <span>Kasir</span>
                        </a>

                        <a
                            href="{{ route('transactions.index') }}"
                            wire:navigate
                            class="flex flex-col items-center justify-center gap-1 min-h-12 rounded-xl px-2 py-1.5 text-xs font-semibold transition-colors {{ request()->routeIs('transactions.*') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <x-ti-list-details class="size-5 {{ request()->routeIs('transactions.*') ? 'text-emerald-700' : 'text-slate-500' }}" aria-hidden="true" />
                            <span>Transaksi</span>
                        </a>

                        <a
                            href="{{ route('customers.index') }}"
                            wire:navigate
                            class="flex flex-col items-center justify-center gap-1 min-h-12 rounded-xl px-2 py-1.5 text-xs font-semibold transition-colors {{ request()->routeIs('customers.*') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <x-ti-users class="size-5 {{ request()->routeIs('customers.*') ? 'text-emerald-700' : 'text-slate-500' }}" aria-hidden="true" />
                            <span>Pelanggan</span>
                        </a>
                    </div>
                </nav>
            @endauth
        </div>

        @livewireScripts
        @stack('scripts')
    </div>
</body>

</html>
