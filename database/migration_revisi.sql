-- ============================================================
-- MIGRATION REVISI - Kopsis POS
-- Jalankan SQL ini di phpMyAdmin (tab SQL) atau terminal MySQL
-- Data lama TIDAK akan hilang
-- ============================================================

-- 1. Tabel transaksi: tambah status, alasan_batal, edited_at
ALTER TABLE `transaksi`
  ADD COLUMN `status` ENUM('selesai','diubah','dibatalkan') NOT NULL DEFAULT 'selesai' AFTER `kembalian`,
  ADD COLUMN `alasan_batal` TEXT NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN `edited_at` DATETIME NULL DEFAULT NULL AFTER `alasan_batal`;

-- Index untuk filter status di laporan
ALTER TABLE `transaksi`
  ADD INDEX `idx_transaksi_status` (`status`);

-- 2. Tabel restock: tambah tipe dan alasan
ALTER TABLE `restock`
  ADD COLUMN `tipe` ENUM('masuk','keluar') NOT NULL DEFAULT 'masuk' AFTER `tanggal`,
  ADD COLUMN `alasan` TEXT NULL DEFAULT NULL AFTER `catatan`;

-- Supplier jadi nullable (karena restock keluar tidak wajib supplier)
ALTER TABLE `restock`
  MODIFY COLUMN `id_supplier` INT UNSIGNED NULL DEFAULT NULL;

-- Index untuk filter tipe restock
ALTER TABLE `restock`
  ADD INDEX `idx_restock_tipe` (`tipe`);

-- ============================================================
-- SELESAI
-- Semua transaksi lama otomatis berstatus 'selesai' (DEFAULT)
-- Semua restock lama otomatis bertipe 'masuk' (DEFAULT)
-- ============================================================
