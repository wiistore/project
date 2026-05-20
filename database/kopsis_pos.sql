-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 20, 2026 at 10:00 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kopsis_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int UNSIGNED NOT NULL,
  `kode_barang` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kategori` int UNSIGNED NOT NULL,
  `satuan` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '5',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama`, `barcode`, `id_kategori`, `satuan`, `harga_jual`, `stok`, `stok_minimum`, `status`, `created_at`, `updated_at`) VALUES
(2, 'brg001', 'pen', 'KPS202605155445', 1, 'pcs', 1999.00, 8, 5, 'aktif', '2026-05-15 12:49:46', '2026-05-16 19:08:48'),
(3, 'BRG002', 'pencil', 'KPS202605167266', 1, 'pcs', 2000.00, 41, 5, 'aktif', '2026-05-16 11:56:52', '2026-05-16 19:08:48'),
(4, 'BRG003', 'Minyak', 'KPS202605166283', 2, 'pcs', 21000.00, 0, 5, 'aktif', '2026-05-16 14:14:24', '2026-05-16 14:14:24');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int UNSIGNED NOT NULL,
  `id_transaksi` int UNSIGNED NOT NULL,
  `id_barang` int UNSIGNED NOT NULL,
  `qty` int NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal_jual` decimal(14,2) NOT NULL,
  `subtotal_beli` decimal(14,2) NOT NULL,
  `laba_item` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_barang`, `qty`, `harga_jual`, `harga_beli`, `subtotal_jual`, `subtotal_beli`, `laba_item`) VALUES
(1, 1, 2, 5, 1999.00, 1000.00, 9995.00, 5000.00, 4995.00),
(2, 2, 2, 5, 1999.00, 1000.00, 9995.00, 5000.00, 4995.00),
(3, 3, 2, 3, 1999.00, 1000.00, 5997.00, 3000.00, 2997.00),
(4, 4, 2, 2, 1999.00, 1000.00, 3998.00, 2000.00, 1998.00),
(5, 5, 2, 3, 1999.00, 1000.00, 5997.00, 3000.00, 2997.00),
(6, 6, 2, 2, 1999.00, 1000.00, 3998.00, 2000.00, 1998.00),
(7, 7, 3, 5, 2000.00, 1000.00, 10000.00, 5000.00, 5000.00),
(8, 8, 3, 4, 2000.00, 1000.00, 8000.00, 4000.00, 4000.00),
(9, 8, 2, 2, 1999.00, 1000.00, 3998.00, 2000.00, 1998.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'ATK', 'alat tulis kantor', '2026-05-15 11:36:07', '2026-05-15 11:36:07'),
(2, 'sembako', '', '2026-05-16 12:25:09', '2026-05-16 12:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `restock`
--

CREATE TABLE `restock` (
  `id` int UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `tipe` enum('masuk','keluar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'masuk',
  `id_barang` int UNSIGNED NOT NULL,
  `id_supplier` int UNSIGNED NULL DEFAULT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `qty` int NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `harga_jual_baru` decimal(12,2) DEFAULT NULL,
  `total_nilai` decimal(14,2) NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restock`
--

INSERT INTO `restock` (`id`, `tanggal`, `tipe`, `id_barang`, `id_supplier`, `id_user`, `qty`, `harga_beli`, `harga_jual_baru`, `total_nilai`, `catatan`, `alasan`, `created_at`) VALUES
(1, '2026-05-15', 'masuk', 2, 1, 1, 20, 1000.00, 1999.00, 20000.00, 'tak taoh', NULL, '2026-05-15 14:21:39'),
(2, '2026-05-16', 'masuk', 3, 2, 1, 50, 1000.00, NULL, 50000.00, 'difarom', NULL, '2026-05-16 12:58:26'),
(3, '2026-05-16', 'masuk', 2, 1, 1, 10, 1000.00, NULL, 10000.00, NULL, NULL, '2026-05-16 14:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kontak_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `nama`, `kontak_person`, `no_hp`, `alamat`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SUMBER ABADII', 'Awie-guys', '01234567890', 'mastrip, jember', 'ttesting', 'aktif', '2026-05-15 11:42:56', '2026-05-15 11:43:22'),
(2, 'sumber berkah', 'dimas', '0912837465573', 'halmahera', 'difarom', 'aktif', '2026-05-16 12:36:55', '2026-05-16 12:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int UNSIGNED NOT NULL,
  `kode_transaksi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_jual` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_beli` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_laba` decimal(14,2) NOT NULL DEFAULT '0.00',
  `metode_bayar` enum('cash','transfer','qris','ewallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal_bayar` decimal(14,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(14,2) NOT NULL DEFAULT '0.00',
  `status` enum('selesai','diubah','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'selesai',
  `alasan_batal` text COLLATE utf8mb4_unicode_ci,
  `edited_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `id_user`, `tanggal`, `total_jual`, `total_beli`, `total_laba`, `metode_bayar`, `nominal_bayar`, `kembalian`, `status`, `alasan_batal`, `edited_at`, `created_at`) VALUES
(1, 'TRX20260515212229283', 1, '2026-05-15 21:22:29', 9995.00, 5000.00, 4995.00, 'cash', 10000.00, 5.00, 'selesai', NULL, NULL, '2026-05-15 14:22:29'),
(2, 'TRX20260515213837462', 1, '2026-05-15 21:38:37', 9995.00, 5000.00, 4995.00, 'cash', 10000.00, 5.00, 'selesai', NULL, NULL, '2026-05-15 14:38:37'),
(3, 'TRX20260515214052235', 1, '2026-05-15 21:40:52', 5997.00, 3000.00, 2997.00, 'cash', 20000.00, 14003.00, 'selesai', NULL, NULL, '2026-05-15 14:40:52'),
(4, 'TRX20260515215612333', 1, '2026-05-15 21:56:12', 3998.00, 2000.00, 1998.00, 'cash', 4999.00, 1001.00, 'selesai', NULL, NULL, '2026-05-15 14:56:12'),
(5, 'TRX20260516022908519', 1, '2026-05-16 02:29:08', 5997.00, 3000.00, 2997.00, 'cash', 10000.00, 4003.00, 'selesai', NULL, NULL, '2026-05-15 19:29:08'),
(6, 'TRX20260516023530980', 1, '2026-05-16 02:35:30', 3998.00, 2000.00, 1998.00, 'cash', 10000.00, 6002.00, 'selesai', NULL, NULL, '2026-05-15 19:35:30'),
(7, 'TRX20260516204153875', 1, '2026-05-16 20:41:53', 10000.00, 5000.00, 5000.00, 'qris', 10000.00, 0.00, 'selesai', NULL, NULL, '2026-05-16 13:41:53'),
(8, 'TRX20260517020848915', 1, '2026-05-17 02:08:48', 11998.00, 6000.00, 5998.00, 'cash', 20000.00, 8002.00, 'selesai', NULL, NULL, '2026-05-16 19:08:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','kasir') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kasir',
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_protected`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@kopsis.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'aktif', '2026-05-15 11:17:34', '2026-05-15 11:17:34'),
(2, 'kasir1', 'kasir@koperasi.com', '$2y$10$BoMywlawZevuays/OI0OReSt8x4daCkh2zJ3u58XFl/0Aj8RYb9zu', 'kasir', 0, 'aktif', '2026-05-15 11:26:45', '2026-05-16 16:01:23'),
(3, 'dimas', 'dimas@gmail.com', '$2y$10$Hk2nOA87IRD11DcemSbZeeqzpdmO9MhtnwYKPrBQqU9iKxV0fJBEW', 'kasir', 0, 'aktif', '2026-05-16 15:46:23', '2026-05-16 15:53:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_barang_stok` (`stok`),
  ADD KEY `idx_barang_kategori` (`id_kategori`),
  ADD KEY `idx_barang_nama` (`nama`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_transaksi` (`id_transaksi`),
  ADD KEY `idx_detail_barang` (`id_barang`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restock`
--
ALTER TABLE `restock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_restock_barang` (`id_barang`),
  ADD KEY `idx_restock_tanggal` (`tanggal`),
  ADD KEY `idx_restock_supplier` (`id_supplier`),
  ADD KEY `fk_restock_user` (`id_user`),
  ADD KEY `idx_restock_tipe` (`tipe`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`),
  ADD KEY `idx_transaksi_user` (`id_user`),
  ADD KEY `idx_transaksi_metode` (`metode_bayar`),
  ADD KEY `idx_transaksi_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restock`
--
ALTER TABLE `restock`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restock`
--
ALTER TABLE `restock`
  ADD CONSTRAINT `fk_restock_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_restock_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_restock_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
