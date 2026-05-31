-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Bulan Mei 2026 pada 10.16
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `satuan` varchar(30) NOT NULL DEFAULT 'pcs',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 5,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama`, `barcode`, `id_kategori`, `satuan`, `harga_jual`, `stok`, `stok_minimum`, `status`, `created_at`, `updated_at`) VALUES
(6, 'BRG001', 'Beras SB Biru 5Kg', NULL, 1, 'sak', 77000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(7, 'BRG002', 'Bimoli 2L', NULL, 1, 'botol', 43000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(8, 'BRG003', 'Fortune 1L', NULL, 1, 'botol', 22000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(9, 'BRG004', 'Gula Lokal 1Kg', NULL, 1, 'kg', 18000.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(10, 'BRG005', 'Garam Daun', NULL, 1, 'pcs', 3500.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(11, 'BRG006', 'Royco Sapi/Ayam', NULL, 1, 'pcs', 500.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(12, 'BRG007', 'Susu Indomilk C & V', NULL, 2, 'pcs', 5981.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:46:39'),
(13, 'BRG008', 'So Klin Deterjen', NULL, 3, 'pcs', 10000.00, 30, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(14, 'BRG009', 'Rinso Cair Refill', NULL, 3, 'pcs', 1000.00, 40, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(15, 'BRG010', 'Listerin 250ml', NULL, 3, 'botol', 25000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(16, 'BRG011', 'Pepsodent 190gr', NULL, 3, 'pcs', 15000.00, 35, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(17, 'BRG012', 'Kanebo', NULL, 3, 'pcs', 15000.00, 8, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(18, 'BRG013', 'Charm Sayap', NULL, 3, 'pcs', 3000.00, 15, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(19, 'BRG014', 'Charm Biasa', NULL, 3, 'pcs', 2000.00, 33, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(20, 'BRG015', 'Mie Sedap Goreng', NULL, 4, 'pcs', 3500.00, 100, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(21, 'ATK001', 'Hitech', NULL, 5, 'pcs', 5000.00, 67, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(22, 'ATK002', 'New Gel', NULL, 5, 'pcs', 3000.00, 132, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(23, 'ATK003', 'Pen Merah', NULL, 5, 'pcs', 3000.00, 12, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(24, 'ATK004', 'Stabillo', NULL, 5, 'pcs', 4000.00, 18, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(25, 'ATK005', 'Spidol Kecil', NULL, 5, 'pcs', 2000.00, 23, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(26, 'ATK006', 'Pen Lilin', NULL, 5, 'pcs', 1500.00, 72, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(27, 'ATK007', 'Fiber Castel 2H', NULL, 5, 'pcs', 5000.00, 36, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(28, 'ATK008', 'Pensil 2B Asli', NULL, 5, 'pcs', 4000.00, 116, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(29, 'ATK009', 'Pensil 2000', NULL, 5, 'pcs', 2000.00, 121, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(30, 'ATK0010', 'Pensil Linko', NULL, 5, 'pcs', 3000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(31, 'ATK0011', 'Glukol', NULL, 5, 'pcs', 2000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(32, 'ATK0012', 'Lem Cair', NULL, 5, 'pcs', 2000.00, 7, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:33:44'),
(33, 'ATK0013', 'Tip X', NULL, 5, 'pcs', 7000.00, 30, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(34, 'ATK0014', 'Glue stick', NULL, 5, 'pcs', 2000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(35, 'ATK0015', 'Rautan', NULL, 5, 'pcs', 2000.00, 121, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(36, 'ATK0016', 'Linko isi pensil', NULL, 5, 'pcs', 2000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(37, 'ATK0017', 'Joyko', NULL, 5, 'pcs', 2000.00, 28, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:32:29'),
(38, 'ATK0018', 'Penghapus', NULL, 5, 'pcs', 2000.00, 120, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(39, 'ATK0019', 'Spidol boardmarker', NULL, 5, 'pcs', 10000.00, 15, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(40, 'ATK020', 'Spidol Boardmarker', NULL, 5, 'pcs', 10000.00, 20, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(41, 'ATK021', 'Isi Staples', NULL, 5, 'pcs', 5000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(42, 'ATK022', 'Solasi', NULL, 5, 'pcs', 2000.00, 15, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(43, 'ATK023', 'Stick', NULL, 5, 'pcs', 3000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(44, 'ATK024', 'Penghapus Papan', NULL, 5, 'pcs', 5000.00, 12, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(45, 'ATK025', 'Peniti', NULL, 5, 'pcs', 2000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(46, 'ATK026', 'Penggaris Jawaban', NULL, 5, 'pcs', 1000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(47, 'ATK027', 'Busur', NULL, 5, 'pcs', 1000.00, 99, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(48, 'ATK028', 'Kertas Origami', NULL, 5, 'pcs', 4000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(49, 'ATK029', 'Penggaris Panjang', NULL, 5, 'pcs', 3000.00, 38, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(50, 'ATK030', 'Buku Matematika', NULL, 5, 'pcs', 4000.00, 31, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(51, 'ATK031', 'Buku Vision Biasa', NULL, 5, 'pcs', 4000.00, 16, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(52, 'ATK032', 'Buku Gambar A3', NULL, 5, 'pcs', 10000.00, 16, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(53, 'ATK033', 'Buku Gambar A4', NULL, 5, 'pcs', 5000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(54, 'ATK034', 'Sampul Plastik', NULL, 5, 'pcs', 4000.00, 177, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:58:52'),
(55, 'ATK035', 'Double Tip', NULL, 5, 'pcs', 4000.00, 6, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:31:00'),
(56, 'ATK036', 'Sticky Note', NULL, 5, 'pcs', 4000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(57, 'ATK037', 'Gunting', NULL, 5, 'pcs', 5000.00, 14, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(58, 'ATK038', 'Silet', NULL, 5, 'pcs', 1000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(59, 'ATK039', 'Hansaplast', NULL, 5, 'pcs', 1000.00, 46, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(60, 'ATK040', 'Sampul Plastik Folio', NULL, 5, 'pcs', 1000.00, 55, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(61, 'ATK041', 'Folio Bergaris', NULL, 5, 'pcs', 1000.00, 73, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(62, 'ATK042', 'Sampul Buku Coklat', NULL, 5, 'pcs', 500.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(63, 'ATK043', 'Map Snell', NULL, 5, 'pcs', 3000.00, 13, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(64, 'ATK044', 'Map Kertas', NULL, 5, 'pcs', 2000.00, 17, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(65, 'ATK045', 'Buffalo Kuning', NULL, 5, 'pcs', 1000.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(66, 'ATK046', 'Buffalo Merah', NULL, 5, 'pcs', 1000.00, 62, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(67, 'ATK047', 'Buffalo Putih', NULL, 5, 'pcs', 1000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(68, 'ATK048', 'Mika ID Card A3', NULL, 5, 'pcs', 3000.00, 283, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(69, 'ATK049', 'Mika ID Card A2', NULL, 5, 'pcs', 3000.00, 22, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(70, 'ATK050', 'Buku Tabungan', NULL, 5, 'pcs', 2000.00, 62, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(71, 'ATK051', 'SKU', NULL, 5, 'pcs', 1500.00, 19, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 09:00:09'),
(72, 'ATK052', 'UUD 1945', NULL, 5, 'pcs', 5000.00, 95, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(73, 'ATK053', 'Kock', NULL, 5, 'pcs', 5000.00, 11, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(74, 'ATK054', 'Kartu Haid Merah', NULL, 5, 'pcs', 2000.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(75, 'ATK055', 'Kartu Haid Kuning', NULL, 5, 'pcs', 2000.00, 75, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(76, 'ATK056', 'Kartu Haid Hijau', NULL, 5, 'pcs', 2000.00, 45, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(77, 'ATK057', 'Masker Duckbill Putih', NULL, 5, 'pcs', 2000.00, 48, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:33:44'),
(78, 'ATK058', 'Masker Hitam', NULL, 5, 'pcs', 2000.00, 30, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(79, 'ATK059', 'Tisu Saku', NULL, 5, 'pcs', 2000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(80, 'ATK060', 'Sampul Mika Putih', NULL, 5, 'pcs', 1000.00, 50, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(81, 'ATK061', 'Sampul Mika Merah', NULL, 5, 'pcs', 1000.00, 75, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(82, 'ATR001', 'Songkok', NULL, 6, 'pcs', 54000.00, 17, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(83, 'ATR002', 'Udeng', NULL, 6, 'pcs', 50000.00, 12, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(84, 'ATR003', 'Hasduk', NULL, 6, 'pcs', 15000.00, 32, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(85, 'ATR004', 'Sarung', NULL, 6, 'pcs', 120000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(86, 'ATR005', 'Batik', NULL, 6, 'pcs', 125000.00, 15, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(87, 'ATR006', 'Kaos Olahraga', NULL, 6, 'pcs', 115000.00, 21, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(88, 'ATR007', 'Jas Almamater', NULL, 6, 'pcs', 170000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(89, 'ATR008', 'Mukena', NULL, 6, 'pcs', 100000.00, 8, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(90, 'ATR009', 'Dasi', NULL, 6, 'pcs', 15000.00, 45, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(91, 'ATR010', 'Topi Sekolah', NULL, 6, 'pcs', 25000.00, 18, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(92, 'ATR011', 'Ikat Pinggang', NULL, 6, 'pcs', 30000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(93, 'ATR012', 'Kaos Kaki Putih', NULL, 6, 'pasang', 10000.00, 55, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(94, 'ATR013', 'Kaos Kaki Hitam', NULL, 6, 'pasang', 10000.00, 38, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(95, 'ATR014', 'Badge OSIS', NULL, 6, 'pcs', 5000.00, 25, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(96, 'ATR015', 'Badge Nama', NULL, 6, 'pcs', 5000.00, 40, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(97, 'ATR016', 'Atribut Pramuka', NULL, 6, 'set', 35000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(98, 'ATR017', 'Peci Hitam', NULL, 6, 'pcs', 20000.00, 14, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(99, 'ATR018', 'Rompi Pramuka', NULL, 6, 'pcs', 85000.00, 10, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(100, 'ATR019', 'Jilbab Sekolah', NULL, 6, 'pcs', 35000.00, 16, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50'),
(101, 'ATR020', 'PIN Sekolah', NULL, 6, 'pcs', 3000.00, 70, 5, 'aktif', '2026-05-29 08:27:50', '2026-05-29 08:27:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_transaksi` int(10) UNSIGNED NOT NULL,
  `id_barang` int(10) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal_jual` decimal(14,2) NOT NULL,
  `subtotal_beli` decimal(14,2) NOT NULL,
  `laba_item` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_barang`, `qty`, `harga_jual`, `harga_beli`, `subtotal_jual`, `subtotal_beli`, `laba_item`) VALUES
(23, 13, 55, 2, 4000.00, 0.00, 8000.00, 0.00, 8000.00),
(24, 14, 55, 2, 4000.00, 0.00, 8000.00, 0.00, 8000.00),
(25, 15, 37, 2, 2000.00, 0.00, 4000.00, 0.00, 4000.00),
(26, 16, 32, 3, 2000.00, 0.00, 6000.00, 0.00, 6000.00),
(27, 16, 77, 2, 2000.00, 0.00, 4000.00, 0.00, 4000.00),
(28, 17, 54, 3, 4000.00, 0.00, 12000.00, 0.00, 12000.00),
(29, 18, 71, 6, 1500.00, 1000.00, 9000.00, 6000.00, 3000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Sembako', 'Produk kebutuhan pokok', '2026-05-29 08:27:05', '2026-05-29 08:27:05'),
(2, 'Minuman', 'Produk minuman', '2026-05-29 08:27:05', '2026-05-29 08:27:05'),
(3, 'Kebersihan', 'Produk kebersihan rumah tangga', '2026-05-29 08:27:05', '2026-05-29 08:27:05'),
(4, 'Makanan', ' Produk makanan', '2026-05-29 08:27:05', '2026-05-29 08:27:05'),
(5, 'ATK', 'Alat tulis kantor dan sekolah', '2026-05-29 08:27:05', '2026-05-29 08:27:05'),
(6, 'Atribut Siswa', 'Perlengkapan atribut siswa sekolah', '2026-05-29 08:27:05', '2026-05-29 08:27:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `restock`
--

CREATE TABLE `restock` (
  `id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `tipe` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `id_barang` int(10) UNSIGNED NOT NULL,
  `id_supplier` int(10) UNSIGNED DEFAULT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `harga_jual_baru` decimal(12,2) DEFAULT NULL,
  `total_nilai` decimal(14,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_transaksi` varchar(30) NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_jual` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_beli` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_laba` decimal(14,2) NOT NULL DEFAULT 0.00,
  `metode_bayar` enum('cash','transfer','qris','ewallet') NOT NULL,
  `nominal_bayar` decimal(14,2) NOT NULL DEFAULT 0.00,
  `kembalian` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('selesai','diubah','dibatalkan') NOT NULL DEFAULT 'selesai',
  `alasan_batal` text DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `is_protected` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_protected`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@kopsis.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'aktif', '2026-05-15 11:17:34', '2026-05-15 11:17:34'),
(2, 'kasir1', 'kasir@koperasi.com', '$2y$10$BoMywlawZevuays/OI0OReSt8x4daCkh2zJ3u58XFl/0Aj8RYb9zu', 'kasir', 0, 'aktif', '2026-05-15 11:26:45', '2026-05-16 16:01:23');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_barang_stok` (`stok`),
  ADD KEY `idx_barang_kategori` (`id_kategori`),
  ADD KEY `idx_barang_nama` (`nama`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_transaksi` (`id_transaksi`),
  ADD KEY `idx_detail_barang` (`id_barang`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `restock`
--
ALTER TABLE `restock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_restock_barang` (`id_barang`),
  ADD KEY `idx_restock_tanggal` (`tanggal`),
  ADD KEY `idx_restock_supplier` (`id_supplier`),
  ADD KEY `fk_restock_user` (`id_user`),
  ADD KEY `idx_restock_tipe` (`tipe`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`),
  ADD KEY `idx_transaksi_user` (`id_user`),
  ADD KEY `idx_transaksi_metode` (`metode_bayar`),
  ADD KEY `idx_transaksi_status` (`status`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `restock`
--
ALTER TABLE `restock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `restock`
--
ALTER TABLE `restock`
  ADD CONSTRAINT `fk_restock_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_restock_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_restock_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
