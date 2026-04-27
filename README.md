# Tugas DW P4 - Dashboard Penjualan (PHP + PostgreSQL + Docker)

Project ini menampilkan visualisasi data warehouse penjualan menggunakan:
- PHP (Apache)
- PostgreSQL
- Chart.js
- Docker Compose

## Fitur

- Dashboard chart pada halaman utama:
  - Total Penjualan per Bulan (line chart)
  - Produk Terlaris (bar chart)
  - Penjualan per Kota (pie chart)
- API sederhana berbasis PHP untuk query data chart
- Seed data otomatis saat container PostgreSQL pertama kali dibuat

## Struktur Project

- `index.php` : UI dashboard dan render chart
- `api.php` : endpoint data chart (JSON)
- `config.php` : koneksi database (PDO) + loader `.env`
- `.env` : konfigurasi database
- `init.sql` : schema + dummy data PostgreSQL
- `Dockerfile` : image PHP Apache + extension `pdo_pgsql`
- `docker-compose.yml` : orkestrasi service app dan PostgreSQL

## Prasyarat

- Docker
- Docker Compose

## Menjalankan Project

1. Masuk ke folder project:

   cd /home/dedoy/kode/tugas-dw-p4

2. Jalankan stack:

   sudo docker compose up -d --build

3. Buka dashboard:

   http://localhost:8000

## Konfigurasi Environment

File `.env` default:

- DB_DRIVER=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_NAME=app_db
- DB_USER=postgres
- DB_PASSWORD=postgres

Catatan:
- Service `app` di Docker Compose otomatis memakai host internal `postgres`.
- Port PostgreSQL di host dipublish ke `5433` (`5433:5432`).

## Endpoint API

- GET `/api.php?chart=sales_by_month`
- GET `/api.php?chart=best_selling_products`
- GET `/api.php?chart=sales_by_city`

Contoh akses langsung:

- http://localhost:8000/api.php?chart=sales_by_month
- http://localhost:8000/api.php?chart=best_selling_products
- http://localhost:8000/api.php?chart=sales_by_city

## Reset Database (Re-seed)

Jika data berubah atau ingin inisialisasi ulang:

sudo docker compose down -v
sudo docker compose up -d --build

Perintah `down -v` akan menghapus volume PostgreSQL sehingga `init.sql` dieksekusi ulang.

## Verifikasi Tabel

Untuk cek tabel sudah terbentuk:

sudo docker compose exec -T postgres psql -U postgres -d app_db -c "\\dt"

Tabel yang diharapkan:
- transaksi
- fact_penjualan
- dim_produk
- dim_waktu
- dim_pelanggan

## Troubleshooting

1. Error `relation ... does not exist`
- Pastikan `init.sql` lengkap
- Lakukan reset database dengan `down -v` lalu `up -d --build`

2. Container `dw-postgres` unhealthy
- Cek log:

  sudo docker compose logs postgres --tail=200

3. Port 8000 bentrok
- Ubah mapping port pada `docker-compose.yml`, contoh `8080:80`

## Author

Project tugas Data Warehouse P4.
