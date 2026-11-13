# DESIGN.md: LondriKita (Aplikasi Penyelenggaraan Laundry)

## 1. Identitas & Karakter Produk
- **Nama Produk**: **LondriKita** (Sistem Kasir & Manajemen Operasional Laundry)
- **Target Pengguna**: **Kasir** di meja depan yang membutuhkan input transaksi cepat dan cetak nota, serta **Pemilik (Admin)** yang mengelola master kategori/layanan, tarif dinamis, audit trail harga, dan poin loyalitas pelanggan.
- **Karakter Visual**: Bersih, segar, terstruktur, dan fokus pada keterbacaan angka transaksi serta kecepatan alur kerja meja kasir.
- **Dials**: `Dial: ENERGY 2 / RHYTHM 2 / MOTION 1`

## 2. Alasan Keputusan Visual (R-31 One-Line Reasons)
- **Mengapa warna ini (R-01, R-29)**: Menggunakan latar netral terang (`slate-50` & putih bersih) dengan identitas utama **Emerald** (`#059669`) untuk aksi utama, tombol simpan, dan navigasi aktif agar terasa segar dan jelas, dipadukan dengan warna semantik status (`sky` untuk proses cuci, `amber` untuk setrika/belum bayar, `violet` untuk siap diambil, `rose` untuk pengurangan/peringatan).
- **Mengapa tipografi ini (R-06)**: Menggunakan **Plus Jakarta Sans** dengan `tabular-nums` pada seluruh nominal Rupiah, berat (kg), dan saldo poin agar kolom angka sejajar rapi dan cepat dipindai oleh kasir.
- **Mengapa tata letak ini (R-05)**: Mengadopsi ritme fungsional (*RHYTHM 2*): halaman Kasir (POS) memakai tata letak 2 kolom (panel input layanan & pelanggan di kiri, ringkasan nota + kalkulasi otomatis di kanan pada desktop; menumpuk rapi di ponsel), sementara halaman Master Data & Audit Trail memakai filter ringkas di atas tabel.
- **Mengapa jarak & sudut ini (R-11)**: Memakai kombinasi sudut membulat terukur (`rounded-2xl` untuk kartu utama, `rounded-xl` untuk tombol dan item navigasi, `rounded-lg` untuk badge status dan input) dengan jarak `p-5` dan `gap-4` agar kepadatan informasi tetap nyaman.
- **Mengapa menggunakan kartu (R-12, R-14)**: Kartu `<x-card>` memisahkan area kerja logis (pemilihan pelanggan, keranjang layanan, rincian biaya tambahan & diskon, serta panel ringkasan grand total) dengan elevasi tipis `shadow-xs` dan garis batas `border-slate-200/80`.
- **Mengapa memilih ikon ini (R-04)**: Menggunakan `secondnetwork/blade-tabler-icons` yang relevan langsung dengan operasional laundry dan kasir (`wash-machine`, `receipt`, `users`, `tag`, `history`, `coins`, `printer`, `plus`, `edit`, `trash`) tanpa ikon dekoratif acak.
- **Mengapa gerakan ini (R-19)**: Menggunakan *MOTION 1* yang tenang berupa transisi hover/fokus standar dan transisi modal yang menghormati `prefers-reduced-motion`, agar meja kasir tetap responsif tanpa distraksi.
