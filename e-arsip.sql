-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2025 at 02:08 PM
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
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kode`
--

CREATE TABLE `kode` (
  `id_kode` bigint UNSIGNED NOT NULL,
  `kode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kode`
--

INSERT INTO `kode` (`id_kode`, `kode`, `description`) VALUES
(1, 'S-K', 'Surat Keputusan'),
(2, 'S-Ket', 'Surat Keterangan'),
(3, 'S-P', 'Surat Peringatan'),
(4, 'P', 'Surat Pengumuman'),
(5, 'S-T', 'Surat Tugas'),
(6, 'S-U', 'Surat Undangan'),
(8, 'S-UK', 'UKK');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2025_09_25_113407_create_roles_table', 1),
(5, '2025_09_25_113409_create_users_table', 1),
(6, '2025_09_25_113410_create_siswa_table', 1),
(7, '2025_09_25_113412_create_surat_table', 1),
(8, '2025_10_14_204302_create_kode_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Kepala Staf', 'Memiliki akses penuh terhadap sistem', '2025-10-14 13:50:10', '2025-10-14 13:50:10'),
(2, 'Staf', 'Hanya dapat mengelola data tertentu', '2025-10-14 13:50:10', '2025-10-14 13:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` bigint UNSIGNED NOT NULL,
  `nama_siswa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rombel` enum('A','B') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_angkatan` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_ppdb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_akte` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ijazah_smp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ijazah_sma` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nama_siswa`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `rombel`, `tahun_angkatan`, `file_ppdb`, `file_kk`, `file_akte`, `file_ktp`, `file_kts`, `file_foto`, `file_ijazah_smp`, `file_ijazah_sma`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Budi Santoso', 'L', 'Jakarta', '2005-08-21', 'Jl. Merdeka No. 12 Jakarta', 'A', '2023', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sulthoni', '2025-10-14 13:50:11', '2025-10-14 13:50:11'),
(2, 'Siti Aminah', 'P', 'Bandung', '2006-01-11', 'Jl. Asia Afrika No. 45 Bandung', 'B', '2023', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmad', '2025-10-14 13:50:11', '2025-10-14 13:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` bigint UNSIGNED NOT NULL,
  `no_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_surat` date NOT NULL,
  `perihal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pengirim` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `file_surat` json DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `no_surat`, `kode_surat`, `jenis_surat`, `tanggal_surat`, `perihal`, `pengirim`, `penerima`, `keterangan`, `file_surat`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '001/SMK/2023', 'S-K', 'Masuk', '2023-09-01', 'Undangan Rapat Koordinasi', 'Dinas Pendidikan Provinsi', 'SMK Negeri 1', 'Rapat membahas agenda tahunan sekolah', '[\"undangan-rapat.pdf\"]', 'Sulthoni', '2025-10-18 14:54:48', '2025-10-18 14:54:48'),
(2, '002/SMK/2023', 'S-Ket', 'Keluar', '2023-09-05', 'Laporan Kegiatan Sekolah', 'SMK Negeri 1', 'Dinas Pendidikan', 'Laporan kegiatan pembelajaran 2023', '[\"laporan-kegiatan.pdf\"]', 'Ahmad', '2025-10-18 14:54:48', '2025-10-18 15:31:10'),
(3, '003/SMK/2023', 'S-T', 'Masuk', '2023-09-10', 'Permohonan Data Siswa', 'Kementerian Pendidikan', 'SMK Negeri 1', 'Diminta data siswa tahun ajaran baru', '[\"permohonan-data.pdf\"]', 'Sulthoni', '2025-10-18 14:54:48', '2025-10-18 14:54:48'),
(4, '004/SMK/2023', 'S-P', 'Keluar', '2023-09-15', 'Surat Pemberitahuan Libur', 'SMK Negeri 1', 'Orang Tua/Wali Murid', 'Pemberitahuan libur semester ganjil', '[\"libur.pdf\"]', 'Ahmad', '2025-10-18 14:54:48', '2025-10-18 14:54:48'),
(15, '098', 'S-K', 'Keluar', '2025-11-06', 'sosoo', 'toniyu', 'inot', NULL, '\"[\\\"surat\\\\/098-s-k-soso-6908c1ce345c1.jpg\\\",\\\"surat\\\\/098-s-k-sosoo-690cc87bb55fd.png\\\"]\"', 'Sulthoni', '2025-11-03 14:53:02', '2025-11-06 16:10:35'),
(18, '8686', 'S-T', 'Keluar', '2025-11-06', 'o,', 'u', 'u', NULL, '\"[]\"', 'Sulthoni', '2025-11-06 15:55:06', '2025-11-06 15:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_role` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `name`, `email`, `password`, `foto`, `id_role`, `created_at`, `updated_at`) VALUES
(1, 'Sulthoni', 'admin@admin.com', '$2y$12$iAPfpmzUTM0WlDRFGwOK9eGiX5qUBN6bRiI0YzV7lDlAnaF.ddqCO', '1762036530-admin.jpg', 1, '2025-10-14 13:50:11', '2025-11-01 22:51:57'),
(2, 'Ahmad', 'staf@admin.com', '$2y$12$hwPlmIPJmM2c0Tr2OPcFaO8syAGnrdbONwVjOQVCfuCq0HO16rB5y', '1762036549-admin.jpg', 2, '2025-10-14 13:50:11', '2025-11-01 22:52:27'),
(6, 'putra', 'putra@putra.com', '$2y$12$bkxV/xWdxZqRdvge1/VF..nCUxlDCDCoHrMdUJM9ATRT3QjWV7NDW', '1762037665-admin.jpg', 2, '2025-11-01 22:54:26', '2025-11-01 22:54:26'),
(7, 'ojie', 'oji@staf.com', '$2y$12$063F6t25yv5dxeehdbU1A.Z8BnCajumnrhfxCouwdv2ljuUm3WIna', '1762039159-admin.jpg', 1, '2025-11-01 23:02:26', '2025-11-01 23:19:19'),
(8, 'okok', 'okok@gmail.com', '$2y$12$Q62TVeosEWBkeAIHTGCq1O3Zbws8VSgNAdFN2ooXId628stLagQvG', '1762038494-admin.jpg', 1, '2025-11-01 23:08:14', '2025-11-01 23:08:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `kode`
--
ALTER TABLE `kode`
  ADD PRIMARY KEY (`id_kode`),
  ADD UNIQUE KEY `kode_kode_unique` (`kode`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_id_role_foreign` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kode`
--
ALTER TABLE `kode`
  MODIFY `id_kode` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
