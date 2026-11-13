<?php

declare(strict_types=1);

use App\Enums\LaundryStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\LaundryTransaction;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Nota Transaksi Laundry')] class extends Component
{
    use Interactions;

    public LaundryTransaction $transaction;
    public string $laundry_status = '';
    public string $payment_status = '';

    /**
     * Memuat detail transaksi beserta relasi pelanggan, kasir, dan item layanan.
     */
    public function mount(LaundryTransaction $transaction): void
    {
        $this->transaction = $transaction->load(['customer', 'user', 'items']);
        $this->laundry_status = $this->transaction->laundry_status->value;
        $this->payment_status = $this->transaction->payment_status->value;
    }

    /**
     * Memperbarui status cucian dan status pembayaran transaksi.
     */
    public function updateStatus(): void
    {
        $this->validate([
            'laundry_status' => 'required|in:' . implode(',', LaundryStatusEnum::values()),
            'payment_status' => 'required|in:' . implode(',', PaymentStatusEnum::values()),
        ]);

        $this->transaction->update([
            'laundry_status' => $this->laundry_status,
            'payment_status' => $this->payment_status,
        ]);

        $this->transaction->refresh()->load(['customer', 'user', 'items']);
        $this->toast()->success('Berhasil', 'Status nota berhasil diperbarui.')->send();
    }
};
