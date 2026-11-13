<?php

declare(strict_types=1);

use App\Livewire\Traits\WithTableX;
use App\Models\PriceHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Audit Trail Perubahan Harga')] class extends Component
{
    use WithTableX;

    public string $sortFieldDefault = 'created_at';
    public string $sortDirectionDefault = 'desc';

    #[Url]
    public string $search_service = '';

    #[Url]
    public string $search_category = '';

    /**
     * Mengambil log riwayat perubahan harga layanan beserta akun admin yang mengubah.
     */
    #[Computed]
    public function dataTable(): LengthAwarePaginator
    {
        return PriceHistory::with('user')
            ->when($this->search_service !== '', fn ($q) => $q->where('service_name', 'like', '%' . $this->search_service . '%'))
            ->when($this->search_category !== '', fn ($q) => $q->where('category_name', 'like', '%' . $this->search_category . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
};
