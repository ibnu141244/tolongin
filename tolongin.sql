-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Sep 2026 pada 08.24
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
-- Database: `tolongin`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `imbalan` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jobs`
--

INSERT INTO `jobs` (`id`, `judul`, `kategori`, `lokasi`, `deskripsi`, `tanggal`, `imbalan`, `status`, `created_at`) VALUES
(1, 'Bantu angkat kulkas', 'Bantuan fisik', 'Kuranji, Padang', 'Membutuhkan 2 orang untuk membantu menurunkan kulkas dari lantai 2.', '2026-09-30 14:00:00', 50000.00, 'OPEN', '2026-09-29 02:42:32'),
(2, 'Antar berkas ke kantor pos', 'Bantuan antar', 'Ulak Karang, Padang', 'Mengantar amplop penting ke kantor pos, pulang bawa bukti pengiriman.', '2026-10-05 10:00:00', 40000.00, 'OPEN', '2026-09-29 02:42:32'),
(3, 'Bantu cari pacar', 'Bantuan mental', 'Marapalam, Padang', 'Butuh teman diskusi. Serius. Imbalan bagus.', '2026-10-30 15:30:00', 100000.00, 'OPEN', '2026-09-29 02:42:32'),
(4, 'Bantu stem motor', 'Bantuan fisik', 'Lubuk Begalung, Padang', 'Antar motor ke bengkel stelem pagi, ambil sore.', '2026-10-29 16:17:00', 17000.00, 'OPEN', '2026-09-29 02:42:32');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
