CREATE DATABASE IF NOT EXISTS produk_umkm_2401020028
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE produk_umkm_2401020028;

DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori_produk;

CREATE TABLE kategori_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_produk CHAR(10) NOT NULL UNIQUE,
  nama_produk VARCHAR(100) NOT NULL,
  sku VARCHAR(30) NOT NULL UNIQUE,
  stok SMALLINT UNSIGNED NOT NULL,
  kategori_produk_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT chk_produk_stok CHECK (stok BETWEEN 0 AND 500),
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_produk_id)
    REFERENCES kategori_produk (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO kategori_produk (nama_kategori) VALUES ('Makanan'), ('Minuman');

INSERT INTO produk (kode_produk, nama_produk, sku, stok, kategori_produk_id, created_at, updated_at) VALUES
('PR00000001', 'Keripik Gonggong', 'SKU-MKN-001', 40, 1, NOW(), NOW()),
('PR00000002', 'Kopi Bubuk Lokal', 'SKU-MNM-001', 25, 2, NOW(), NOW()),
('PR00000003', 'Kue Bangkit',      'SKU-MKN-002', 30, 1, NOW(), NOW());
