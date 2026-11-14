<?php

declare(strict_types=1);

use App\Enums\LaundryStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Livewire\Traits\WithTableX;
use App\Models\LaundryTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Riwayat Transaksi Laundry')] class extends Component
{
    use Interactions, WithTableX;

    public string $sortFieldDefault = 'created_at';
    public string $sortDirectionDefault = 'desc';

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'invoice_number', 'laundry_status', 'payment_status', 'grand_total'];

    #[Url]
    public string $search_invoice = '';

    #[Url]
    public string $search_customer = '';

    #[Url]
    public ?string $search_laundry_status = null;

    #[Url]
    public ?string $search_payment_status = null;

    /**
     * Mengambil daftar transaksi laundry dengan filter pencarian, status, dan paginasi.
     */
    #[Computed]
    public function dataTable(): LengthAwarePaginator
    {
        return LaundryTransaction::with(['customer', 'user', 'items'])
            ->when($this->search_invoice !== '', fn ($q) => $q->where('invoice_number', 'like', '%' . $this->search_invoice . '%'))
            ->when($this->search_customer !== '', fn ($q) => $q->whereHas('customer', function ($cq): void {
                $cq->where('name', 'like', '%' . $this->search_customer . '%')
                    ->orWhere('phone', 'like', '%' . $this->search_customer . '%');
            }))
            ->when($this->search_laundry_status, fn ($q) => $q->where('laundry_status', $this->search_laundry_status))
            ->when($this->search_payment_status, fn ($q) => $q->where('payment_status', $this->search_payment_status))
            ->orderBy($this->safeSortField(), $this->safeSortDirection())
            ->paginate($this->perPage);
    }

    /**
     * Memperbarui status cucian langsung dari daftar transaksi.
     */
    public function updateLaundryStatus(int $transactionId, string $status): void
    {
        $enum = LaundryStatusEnum::tryFrom($status);
        if (! $enum) {
            return;
        }

        $transaction = LaundryTransaction::query()->findOrFail($transactionId);
        $transaction->update(['laundry_status' => $enum->value]);

        $this->toast()->success('Berhasil', 'Status cucian ' . $transaction->invoice_number . ' diubah ke ' . $enum->label() . '.')->send();
    }

    /**
     * Menandai transaksi sebagai lunas.
     */
    public function markAsPaid(int $transactionId): void
    {
        $transaction = LaundryTransaction::query()->findOrFail($transactionId);
        $transaction->update(['payment_status' => PaymentStatusEnum::PAID->value]);

        $this->toast()->success('Berhasil', 'Pembayaran ' . $transaction->invoice_number . ' ditandai Lunas.')->send();
    }
};
