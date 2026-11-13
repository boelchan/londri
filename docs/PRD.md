# Product Requirements Document (PRD): LondriKita (Aplikasi Penyelenggaraan Laundry)

## 1. Gambaran Umum Sistem
**LondriKita** adalah aplikasi pencatatan operasional dan kasir laundry berbasis Laravel 13 dan Livewire 4. Sistem dirancang untuk mempercepat alur kerja kasir di meja depan sekaligus memberikan kendali penuh kepada Pemilik (Admin) atas master data layanan, tarif dinamis, audit trail perubahan harga, dan manajemen poin loyalitas pelanggan.

## 2. Hak Akses Pengguna (Role)
1. **Kasir (`cashier`)**:
   - Mengakses Beranda Operasional, Kasir (POS), Riwayat Transaksi, Detail/Cetak Nota, dan Master Pelanggan.
   - Menambah pelanggan baru secara cepat dari halaman kasir maupun halaman pelanggan.
   - Mengubah status cucian (`Diterima`, `Dicuci`, `Disetrika`, `Siap Diambil`, `Selesai`) dan status pembayaran (`Lunas` / `Belum Bayar`).
2. **Pemilik / Admin (`administrator`)**:
   - Memiliki seluruh hak akses Kasir.
   - Mengelola master kategori cucian dan jenis layanan beserta tarif harga dinamis (`/catalog`).
   - Memantau log Audit Trail perubahan harga layanan (`/price-histories`).
   - Melakukan penyesuaian poin loyalitas pelanggan secara manual (penambahan atau pengurangan/penukaran poin dengan catatan alasan).
   - Mengelola akun pengguna Kasir dan Pemilik (`/users`).

## 3. Alur Input Transaksi (Kasir / POS)
1. **Master Pelanggan**: Memilih pelanggan terdaftar melalui dropdown pencarian atau mendaftarkan pelanggan baru (Nama, Nomor Telepon, Alamat) melalui modal cepat tanpa meninggalkan halaman transaksi.
2. **Kategori & Jenis Layanan**: Memilih kategori cucian (mis. Kiloan, Satuan, Karpet, Sepatu) dan jenis layanan aktif di kategori tersebut. Mendukung satu atau lebih item layanan dalam satu nota.
3. **Volume (KG / Qty)**: Memasukkan berat (kg, mendukung desimal) atau jumlah satuan. Subtotal dihitung otomatis berdasarkan tarif aktif saat transaksi dibuat dan disimpan sebagai snapshot harga pada item nota.
4. **Komponen Tambahan & Diskon**:
   - **Diskon**: Pilihan Tanpa Diskon, Nominal (Rp), atau Persentase (%).
   - **Biaya Tambahan Air**: Komponen opsional untuk pakaian sangat kotor/kondisi khusus.
   - **Biaya Asisten**: Komponen opsional untuk penanganan khusus atau tambahan tenaga.
5. **Finalisasi & Cetak Nota**: Grand Total dihitung otomatis (`Subtotal - Diskon + Biaya Tambahan Air + Biaya Asisten`). Setelah disimpan dalam transaksi database (`DB::transaction`), sistem otomatis menambahkan poin loyalitas ke pelanggan (1 poin per kelipatan Rp 10.000) dan mengarahkan ke halaman Nota siap cetak (`/transactions/{transaction}`).

## 4. Fitur Khusus, Rekap Keuangan & Audit Trail
- **Rekap Kasir Hari Ini di Menu Kasir (`/pos`)**: Kasir dapat langsung memantau jumlah nota, total uang masuk (Lunas) dengan rincian tunai vs non-tunai, piutang (Belum Bayar), serta daftar transaksi yang diproses pada hari tersebut (Pemilik dapat memilih kasir tertentu).
- **Rekap Transaksi & Keuangan (`/recap`)**: Dapat diakses oleh Pemilik maupun Kasir. Pemilik melihat seluruh transaksi dengan filter per bulan, rentang tanggal, per kasir, status pembayaran, ringkasan uang masuk, pengeluaran kantor, kas bersih, dan tabel rekap per kasir. Kasir melihat rekap transaksinya sendiri (`user_id` terfilter otomatis) per tanggal, per bulan, status pembayaran, dan jumlah uang masuk pribadinya.
- **Pengeluaran Kantor (`/expenses`, Khusus Pemilik)**: Pencatatan pengeluaran operasional kantor berkategori (`ExpenseCategoryEnum`: Deterjen/Pewangi/Kimia, Listrik/Air/Internet, Gaji/Bonus, Perawatan Mesin, Kemasan, Sewa Tempat, Operasional Lainnya) lengkap dengan filter bulan, kategori, pencarian keterangan, serta aksi ubah dan hapus.
- **Sistem Poin Loyalitas Fleksibel**: Penambahan poin otomatis saat transaksi selesai dan penyesuaian manual oleh Pemilik (Admin), keduanya tercatat di tabel `point_histories`.
- **Manajemen Tarif Dinamis & Audit Trail Harga**: Setiap perubahan tarif pada master layanan oleh Pemilik otomatis merekam `service_id`, `category_name`, `service_name`, `old_price`, `new_price`, `user_id` (admin pengubah), `notes`, dan `created_at` ke tabel `price_histories`.
