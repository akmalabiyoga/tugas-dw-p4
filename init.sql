-- this SQL is using PostgreSQL syntax

CREATE TABLE transaksi (
    id SERIAL PRIMARY KEY
    , tanggal DATE
    , id_produk INT
    , id_pelanggan INT
    , jumlah INT
    , total_harga DECIMAL(10, 2)
);

CREATE TABLE fact_penjualan (
    id_produk INT
    , id_waktu INT
    , id_pelanggan INT
    , jumlah INT
    , total_harga DECIMAL(10, 2)
);

CREATE TABLE dim_produk (
    id_produk INT
    , nama_produk VARCHAR(255)
    , kategori VARCHAR(255)
);

CREATE TABLE dim_waktu (
    id_waktu INT
    , tanggal DATE
    , bulan INT
    , tahun INT
);

CREATE TABLE dim_pelanggan (
    id_pelanggan INT
    , nama VARCHAR(255)
    , kota VARCHAR(255)
);

INSERT INTO dim_produk (id_produk, nama_produk, kategori) VALUES
(1, 'Laptop', 'Elektronik'),
(2, 'Smartphone', 'Elektronik'),
(3, 'Meja', 'Furniture'),
(4, 'Kursi', 'Furniture'),
(5, 'Buku', 'Alat Tulis');

INSERT INTO dim_waktu (id_waktu, tanggal, bulan, tahun) VALUES
(1, '2024-01-01', 1, 2024),
(2, '2024-02-01', 2, 2024),
(3, '2024-03-01', 3, 2024),
(4, '2024-04-01', 4, 2024),
(5, '2024-05-01', 5, 2024),
(6, '2024-01-15', 1, 2024),
(7, '2024-02-15', 2, 2024),
(8, '2024-03-15', 3, 2024),
(9, '2024-04-15', 4, 2024),
(10, '2024-05-15', 5, 2024);

INSERT INTO dim_pelanggan (id_pelanggan, nama, kota) VALUES
(1, 'Alice', 'Jakarta'),
(2, 'Bob', 'Bandung'),
(3, 'Charlie', 'Surabaya'),
(4, 'David', 'Medan'),
(5, 'Eve', 'Yogyakarta');

INSERT INTO transaksi (tanggal, id_produk, id_pelanggan, jumlah, total_harga) VALUES
('2024-01-01', 1, 1, 2, 2000.00),
('2024-02-01', 2, 2, 1, 500.00),
('2024-03-01', 3, 3, 4, 150.00),
('2024-04-01', 4, 4, 2, 300.00),
('2024-05-01', 5, 5, 3, 75.00),
('2024-01-15', 1, 2, 1, 1000.00),
('2024-02-15', 2, 3, 2, 1000.00),
('2024-03-15', 3, 4, 1, 50.00),
('2024-04-15', 4, 5, 3, 450.00),
('2024-05-15', 5, 1, 2, 50.00);

INSERT INTO fact_penjualan (id_produk, id_waktu, id_pelanggan, jumlah, total_harga)
SELECT t.id_produk, w.id_waktu, p.id_pelanggan, t.jumlah, t.total_harga
FROM transaksi t
JOIN dim_waktu w ON t.tanggal = w.tanggal
JOIN dim_pelanggan p ON t.id_pelanggan = p.id_pelanggan;

