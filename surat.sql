-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 15, 2025 at 06:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-arsip`
--

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id_surat` bigint UNSIGNED NOT NULL,
  `no_surat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_surat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_surat` enum('Masuk','Keluar') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_surat` date NOT NULL,
  `perihal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pengirim` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penerima` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` bigint UNSIGNED DEFAULT NULL,
  `file_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id_surat`, `no_surat`, `kode_surat`, `jenis_surat`, `tanggal_surat`, `perihal`, `pengirim`, `penerima`, `id_user`, `file_surat`, `created_at`, `updated_at`) VALUES
(1, '001/A/2025', 'A-01', 'Masuk', '2025-01-15', 'Undangan Rapat', 'Dinas Pendidikan', 'Sekolah X', 1, NULL, '2025-09-12 10:06:26', '2025-09-12 10:06:26'),
(2, '002/B/2025', 'B-02', 'Keluar', '2025-02-01', 'Laporan Bulanan', 'Sekolah X', 'Dinas Pendidikan', 2, NULL, '2025-09-12 10:06:26', '2025-09-12 10:06:26'),
(17, 'pp', 'pp', 'Masuk', '2025-09-13', 'p', 'o', 'o', 1, 'surat/2mrm2PkY9ZUpbefBnuC8IZv2fBy5pNIYmBJzdmOt.png', '2025-09-13 05:55:42', '2025-09-13 05:55:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id_surat`),
  ADD KEY `surat_id_user_foreign` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id_surat` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `surat_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
