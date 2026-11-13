<?php

declare(strict_types=1);

use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? to_route('home') : to_route('login');
});

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::livewire('/login', 'pages::auth.login')->name('login')->middleware('guest');

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Area Terautentikasi (Kasir & Pemilik)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    /* Beranda Operasional */
    Route::livewire('/home', 'pages::home')->name('home');
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');

    /* Kasir (POS) - Input Transaksi Cepat */
    Route::livewire('/pos', 'pages::pos.index')->name('pos');

    /* Riwayat Transaksi & Cetak Nota */
    Route::livewire('/transactions', 'pages::transactions.index')->name('transactions.index');
    Route::livewire('/transactions/{transaction}', 'pages::transactions.show')->name('transactions.show');

    /* Master Pelanggan & Poin Loyalitas */
    Route::livewire('/customers', 'pages::customers.index')->name('customers.index');

    /* Rekap Transaksi (Pemilik: Semua & Keuangan; Kasir: Rekap Pribadi) */
    Route::livewire('/recap', 'pages::recap.index')->name('recap.index');

    /* Profil & Akun */
    Route::livewire('/account', 'pages::account.index')->name('account');

    /* Panel Khusus Pemilik (Admin) */
    Route::middleware('role:administrator')->group(function () {
        /* Pengeluaran Kantor */
        Route::livewire('/expenses', 'pages::expenses.index')->name('expenses.index');

        /* Manajemen Kategori & Jenis Layanan (Harga Dinamis) */
        Route::livewire('/catalog', 'pages::catalog.index')->name('catalog.index');

        /* Audit Trail Riwayat Perubahan Harga */
        Route::livewire('/price-histories', 'pages::price-histories.index')->name('price-histories.index');

        /* Kelola Pengguna (Kasir & Pemilik) */
        Route::livewire('/users', 'pages::user.index')->name('user.index');
        Route::livewire('/users/create', 'pages::user.create')->name('user.create');
        Route::livewire('/users/{user}/edit', 'pages::user.edit')->name('user.edit');
    });
});
