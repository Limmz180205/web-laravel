# FlipPlay API

FlipPlay mengembangkan simulasi OOP Modul 3 menjadi REST API dengan Laravel 12 dan Eloquent. Kode simulasi terminal lama tetap tersedia dan dapat dijalankan terpisah memakai `php index.php`.

## Menjalankan aplikasi

Kebutuhan: PHP 8.2+, Composer, dan ekstensi PDO SQLite.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
php artisan serve
```

Server berjalan di `http://127.0.0.1:8000`. Endpoint API menggunakan prefix `/api`. Untuk melihat semua route, jalankan `php artisan route:list --path=api`.

## Model data

- `users`: pengguna FlipPlay (memakai migration bawaan Laravel).
- `media`: judul, tipe, tahun, skor, genre, status, jumlah episode/bab/volume, dan studio/negara/penulis.
- `media_user`: pivot koleksi, progress, timestamp, foreign key cascade, dan primary key gabungan.

Tipe yang diterima: `anime`, `drama`, `manga`, `light_novel`. Judul unik per tipe media sehingga Anime dan Manga dengan judul sama tetap dapat dimasukkan seperti pada model Modul 3.

## Endpoint

Semua request body menggunakan JSON.

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/media` | Daftar media; filter opsional `?search=frieren&type=anime&status=finished` |
| POST | `/api/media` | Membuat media |
| GET | `/api/media/{id}` | Detail media dan pengguna koleksinya |
| PUT/PATCH | `/api/media/{id}` | Memperbarui media |
| DELETE | `/api/media/{id}` | Menghapus media |
| POST | `/api/users` | Membuat pengguna |
| GET | `/api/users/{id}` | Detail pengguna dan koleksinya |
| GET | `/api/users/{id}/collection` | Melihat koleksi pengguna |
| POST | `/api/users/{id}/collection/{mediaId}` | Menambahkan media ke koleksi (duplikat: `409`) |
| DELETE | `/api/users/{id}/collection/{mediaId}` | Menghapus media dari koleksi |

Validasi gagal mengembalikan `422` beserta detail error. Media wajib memiliki judul, tipe, tahun, skor 0–10, minimal satu genre, status, dan jumlah unit. Judul yang sama untuk tipe yang sama ditolak.

### Contoh request

Membuat media:

```json
{
  "title": "Frieren: Beyond Journey's End",
  "type": "anime",
  "year": 2023,
  "score": 9.1,
  "genres": ["Adventure", "Drama", "Fantasy"],
  "status": "finished",
  "total_units": 28,
  "creator": "Madhouse"
}
```

Alur koleksi di Postman:

1. `POST /api/users` dengan `{"name":"Demo User","email":"demo@example.com","password":"password123"}`.
2. `POST /api/media` menggunakan contoh di atas.
3. Tambahkan `POST /api/users/{userId}/collection/{mediaId}`. Ulangi request untuk memastikan status `409`.
4. Verifikasi koleksi melalui `GET /api/users/{userId}/collection`.
5. Hapus melalui `DELETE /api/users/{userId}/collection/{mediaId}`.
6. Coba `POST /api/media` dengan `title` kosong untuk memastikan respons validasi `422`.

### Pengujian

```powershell
php artisan test
```

Pengujian fitur menggunakan SQLite in-memory dan mencakup CRUD media, pencarian, validasi judul, serta alur koleksi termasuk duplikasi.
