<?php

namespace App\Livewire\Traits;

use Livewire\WithPagination;

trait WithTableX
{
    use WithPagination;

    // public function queryStringWithTableX()
    // {
    //     $qs = [];
    //     foreach (get_object_vars($this) as $property => $value) {
    //         if (str_starts_with($property, 'search_')) {
    //             $qs[$property] = ['except' => ''];
    //         }
    //     }
    //     return $qs;
    // }

    public function mountWithTableX()
    {
        if (property_exists($this, 'sortFieldDefault')) {
            $this->sortField = $this->sortFieldDefault;
        }

        if (property_exists($this, 'sortDirectionDefault')) {
            $this->sortDirection = $this->sortDirectionDefault;
        }
    }

    public $perPage = 50;

    public bool $open = false;

    public $sortField = 'id';

    public $sortDirection = 'desc';

    public function sortBy($field)
    {
        if (! in_array($field, $this->allowedSortFields(), true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Mengembalikan daftar kolom pengurutan yang diizinkan untuk komponen tabel ini.
     *
     * @return array<int, string>
     */
    protected function allowedSortFields(): array
    {
        if (property_exists($this, 'sortableFields') && is_array($this->sortableFields)) {
            return $this->sortableFields;
        }

        return [$this->defaultSortField()];
    }

    /**
     * Mengembalikan kolom pengurutan bawaan komponen.
     */
    protected function defaultSortField(): string
    {
        return property_exists($this, 'sortFieldDefault') ? (string) $this->sortFieldDefault : 'id';
    }

    /**
     * Mengembalikan kolom pengurutan yang telah divalidasi terhadap allowlist.
     */
    public function safeSortField(): string
    {
        $field = (string) $this->sortField;

        return in_array($field, $this->allowedSortFields(), true)
            ? $field
            : $this->defaultSortField();
    }

    /**
     * Mengembalikan arah pengurutan yang aman ('asc' atau 'desc').
     */
    public function safeSortDirection(): string
    {
        return strtolower((string) $this->sortDirection) === 'asc' ? 'asc' : 'desc';
    }

    public function updatingPerPage()
    {
        $this->resetPage(); // supaya balik ke page 1 kalau perPage diubah
    }

    public function updated($property)
    {
        if (str_starts_with($property, 'search_')) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        foreach (get_object_vars($this) as $property => $value) {
            if (str_starts_with($property, 'search_')) {
                $this->$property = null;
            }
        }
    }

    public function paginationView()
    {
        return 'vendor.pagination.custom';
    }
}
