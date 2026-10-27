# Sistem Data Produk UMKM (Tipe C)

Praktikum Pemrograman Web (INF11197) - Studi Kasus Modul 1-7
NIM: 2401020028

Aplikasi CRUD produk UMKM berbasis Laravel, Blade, dan MySQL/MariaDB. Seluruh logika ada di `routes/web.php` dan tampilan di `resources/views/produk` (tanpa model, migration, controller, atau helper). Akses basis data memakai PDO (prepared statement), tanpa CSS/JavaScript.

## Struktur Data

- `kategori_produk` (1) ---< (N) `produk`
- Relasi: `produk.kategori_produk_id` -> `kategori_produk.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- Nilai unik pada `produk`: `kode_produk` dan `sku`
- CHECK: `stok BETWEEN 0 AND 500`

## Cara Instal

```bash
composer install
cp .env.example .env
php artisan key:generate
```

1. Atur `DB_USERNAME` dan `DB_PASSWORD` di `.env` (nama basis data: `produk_umkm_2401020028`).
2. Impor skema dan data awal (MySQL >= 8.0.16 atau MariaDB >= 10.2.1):
   ```bash
   mysql -u root -p < database/produk_umkm.sql
   ```
3. Jalankan server:
   ```bash
   php artisan serve
   ```
4. Buka `http://127.0.0.1:8000/produk`.

## Daftar Route

| Method | URI | Fungsi |
|---|---|---|
| GET | `/produk` | Indeks, tambah, pencarian (`q`), filter (`kategori`) |
| POST | `/produk` | Create |
| GET | `/produk/{id}/edit` | Form edit |
| PUT | `/produk/{id}` | Update |
| DELETE | `/produk/{id}` | Delete |

## Validasi

- Kode produk: tepat 10 karakter alfanumerik
- Nama produk: 3-100 karakter
- SKU: 5-30 karakter
- Stok: bilangan bulat 0-500
- Kategori: wajib dan harus tersedia

## Keamanan

- Semua query dengan input pengguna memakai `prepare()` dan `execute()`
- Input teks disanitasi dengan `trim()` dan `strip_tags()`
- Form memakai `@csrf`, dan `@method('PUT')` / `@method('DELETE')`
- Kesalahan dicatat lewat `report()`, pengguna hanya melihat pesan singkat
- File `.env` tidak masuk repository
