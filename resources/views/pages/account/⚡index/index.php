<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new #[Title('Profil & Akun')] class extends Component
{
    use Interactions;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Inisialisasi data profil pengguna yang sedang masuk.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = $user->phone;
    }

    /**
     * Memperbarui informasi profil pengguna.
     */
    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah digunakan akun lain.',
        ]);

        $user->update($validated);
        $this->toast()->success('Berhasil', 'Profil akun berhasil diperbarui.')->send();
    }

    /**
     * Memperbarui kata sandi pengguna.
     */
    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->toast()->success('Berhasil', 'Kata sandi berhasil diperbarui.')->send();
    }
};
