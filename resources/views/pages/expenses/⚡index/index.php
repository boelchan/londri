<?php

declare(strict_types=1);

use App\Enums\ExpenseCategoryEnum;
use App\Livewire\Traits\WithTableX;
use App\Models\OfficeExpense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Pengeluaran Kantor')] class extends Component
{
    use Interactions, WithTableX;

    public string $sortFieldDefault = 'expense_date';
    public string $sortDirectionDefault = 'desc';

    /** @var array<int, string> */
    protected array $sortableFields = ['expense_date', 'category', 'amount'];

    #[Url]
    public string $search_description = '';

    #[Url]
    public ?string $search_category = null;

    #[Url]
    public string $search_month = '';

    /* Modal Catat / Ubah Pengeluaran */
    public bool $expenseModal = false;
    public ?int $editingExpenseId = null;
    public string $expense_date = '';
    public string $category = ExpenseCategoryEnum::DETERGENT_CHEMICAL->value;
    public string|int|float $amount = 0;
    public string $description = '';
    public string $notes = '';

    /**
     * Menetapkan bulan berjalan sebagai filter awal.
     */
    public function mount(): void
    {
        if ($this->search_month === '') {
            $this->search_month = now()->format('Y-m');
        }
    }

    /**
     * Mengambil opsi 12 bulan terakhir untuk filter pengeluaran.
     *
     * @return array<int, array{label: string, value: string}>
     */
    #[Computed]
    public function monthOptions(): array
    {
        $options = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->startOfMonth()->subMonths($i);
            $options[] = [
                'label' => $date->translatedFormat('F Y'),
                'value' => $date->format('Y-m'),
            ];
        }

        return $options;
    }

    /**
     * Membangun query pengeluaran kantor sesuai filter aktif.
     *
     * @return Builder<OfficeExpense>
     */
    private function filteredQuery(): Builder
    {
        return OfficeExpense::query()
            ->when($this->search_description !== '', fn (Builder $q) => $q->where('description', 'like', '%' . $this->search_description . '%'))
            ->when($this->search_category, fn (Builder $q) => $q->where('category', $this->search_category))
            ->when($this->search_month !== '', function (Builder $q): void {
                [$year, $month] = array_pad(explode('-', $this->search_month), 2, null);
                if ($year && $month) {
                    $q->whereYear('expense_date', (int) $year)->whereMonth('expense_date', (int) $month);
                }
            });
    }

    /**
     * Menghitung total nominal pengeluaran sesuai filter aktif.
     */
    #[Computed]
    public function totalFilteredAmount(): int
    {
        return (int) $this->filteredQuery()->sum('amount');
    }

    /**
     * Mengambil daftar pengeluaran kantor dengan paginasi.
     */
    #[Computed]
    public function dataTable(): LengthAwarePaginator
    {
        return $this->filteredQuery()
            ->with('user')
            ->orderBy($this->safeSortField(), $this->safeSortDirection())
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    /**
     * Membuka modal untuk mencatat pengeluaran kantor baru.
     */
    public function openCreateModal(): void
    {
        $this->reset(['editingExpenseId', 'description', 'notes']);
        $this->expense_date = now()->format('Y-m-d');
        $this->category = ExpenseCategoryEnum::DETERGENT_CHEMICAL->value;
        $this->amount = 0;
        $this->resetValidation();
        $this->expenseModal = true;
    }

    /**
     * Membuka modal untuk mengubah data pengeluaran kantor.
     */
    public function openEditModal(int $id): void
    {
        $expense = OfficeExpense::query()->findOrFail($id);
        $this->editingExpenseId = $expense->id;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->category = $expense->category->value;
        $this->amount = (int) $expense->amount;
        $this->description = $expense->description;
        $this->notes = (string) ($expense->notes ?? '');
        $this->resetValidation();
        $this->expenseModal = true;
    }

    /**
     * Menyimpan catatan pengeluaran kantor baru atau perubahan data.
     */
    public function saveExpense(): void
    {
        $this->validate([
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', ExpenseCategoryEnum::values()),
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ], [
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi.',
            'category.required' => 'Kategori pengeluaran wajib dipilih.',
            'description.required' => 'Keterangan pengeluaran wajib diisi.',
        ]);

        $amountInt = $this->parseMoney($this->amount);
        if ($amountInt <= 0) {
            $this->addError('amount', 'Nominal pengeluaran harus lebih dari 0.');
            return;
        }

        $payload = [
            'user_id' => Auth::id(),
            'expense_date' => $this->expense_date,
            'category' => $this->category,
            'amount' => $amountInt,
            'description' => trim($this->description),
            'notes' => $this->notes !== '' ? trim($this->notes) : null,
        ];

        if ($this->editingExpenseId) {
            $expense = OfficeExpense::query()->findOrFail($this->editingExpenseId);
            $expense->update($payload);
            $this->toast()->success('Berhasil', 'Pengeluaran kantor berhasil diperbarui.')->send();
        } else {
            OfficeExpense::create($payload);
            $this->toast()->success('Berhasil', 'Pengeluaran kantor berhasil dicatat.')->send();
        }

        $this->expenseModal = false;
        unset($this->dataTable, $this->totalFilteredAmount);
    }

    /**
     * Menghapus catatan pengeluaran kantor setelah konfirmasi.
     */
    public function delete(int $id): void
    {
        $expense = OfficeExpense::query()->findOrFail($id);
        $expense->delete();

        unset($this->dataTable, $this->totalFilteredAmount);
        $this->toast()->success('Berhasil', 'Catatan pengeluaran berhasil dihapus.')->send();
    }

    /**
     * Mengubah input nominal terformat menjadi integer rupiah.
     */
    private function parseMoney(string|int|float|null $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        return $digits === '' || $digits === null ? 0 : (int) $digits;
    }
};
