# MosqueHub

Sistem informasi manajemen masjid berbasis Laravel. Aplikasi mendukung data
terpisah per masjid, pengelolaan jamaah dan kegiatan, keuangan, kepengurusan,
publikasi informasi, serta backup.

## Menjalankan proyek

Kebutuhan utama: PHP 8.3+, Composer, Node.js, dan MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Atur koneksi database dan `APP_URL` pada `.env` sebelum menjalankan migrasi.
Untuk pengembangan lokal, jalankan server Laravel dan Vite pada terminal
terpisah.

```bash
php artisan serve
npm run dev
```

## Pemeriksaan sebelum mengirim perubahan

```bash
php artisan test
./vendor/bin/pint --test
```

Suite test memakai SQLite in-memory, sehingga tidak mengubah database lokal.

## Peta kode

| Lokasi | Tanggung jawab |
| --- | --- |
| `app/Http/Controllers` | Request/response dan koordinasi halaman atau endpoint |
| `app/Http/Requests` | Aturan validasi request |
| `app/Models` | Model dan relasi Eloquent |
| `app/Models/Concerns/BelongsToMosque.php` | Scope serta route binding aman per masjid |
| `app/Services` | Proses bisnis yang dipakai lintas controller, seperti laporan dan backup |
| `app/Support/SiteContext.php` | Resolusi ID masjid aktif |
| `resources/views` | Blade template |
| `public/assets/js` | JavaScript per halaman saat ini |
| `tests/Feature` | Uji perilaku HTTP, akses, dan isolasi data |

## Aturan penting pengembangan

- Semua data milik masjid harus memiliki `mosque_id`, memakai trait
  `BelongsToMosque`, dan diambil melalui scope `forMosque()`.
- Jangan mengambil record milik masjid dengan `Model::find($id)` tanpa scope
  masjid. Untuk route model binding, gunakan model yang sudah memakai trait.
- Jangan menyimpan model Eloquent ke cache. Cache aplikasi dikonfigurasi untuk
  tidak melakukan unserialize class PHP; simpan nilai sederhana/array bila
  caching memang diperlukan.
- Gunakan `FormRequest` untuk validasi yang tidak sepele. Operasi yang mengubah
  beberapa tabel harus memakai transaksi database di service.
- Tambahkan test untuk perubahan pada izin, isolasi antar masjid, dan endpoint
  yang mengubah data.

## Hak akses

Semua halaman internal dilindungi `auth`, `active.user`, `menu.access`, dan
`activity.log`. Role `Ketua YMBPK` memiliki akses penuh; pengguna lain harus
memiliki permission menu yang sesuai. Perubahan pada izin harus disertai test
di `tests/Feature`.

## Backup

Backup otomatis dibuat melalui perintah berikut dan dijadwalkan setiap hari
pukul 01:00:

```bash
php artisan mosquehub:backup
```

Frekuensi tiap masjid diatur melalui Pengaturan Umum. File backup disimpan pada
storage aplikasi; pastikan storage tersebut masuk ke prosedur backup server.

## Konvensi Git

Pisahkan perubahan dalam commit kecil berdasarkan satu tujuan, misalnya
`fix: isolate qurban payment member`, `feat: add report export`, atau
`test: cover mosque isolation`. Hindari mencampur refactor, fitur, dan perubahan
aset besar dalam satu commit.
