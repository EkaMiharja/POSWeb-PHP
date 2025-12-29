-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Des 2025 pada 01.53
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
-- Database: `penjualan_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `tanggal`) VALUES
(1, 'Makanan', '2025-10-20 17:55:00'),
(2, 'Minuman', '2025-10-20 18:02:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `diskon` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `nama_depan` varchar(100) NOT NULL,
  `nama_belakang` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_handphone` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profile`
--

INSERT INTO `profile` (`id`, `nama_depan`, `nama_belakang`, `email`, `alamat`, `no_handphone`, `username`) VALUES
(1, 'w', 'w', 'people@gmail.com', 'ded', '1212', ''),
(2, 'Eka', 'Miharja', 'ek@gmail.com', 'Jl. Duta Kencana No.5', '08999978979', 'Eka');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `nama_produk` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `order_number`, `customer_name`, `nama_produk`, `payment_method`, `total`, `tanggal`) VALUES
(1, '#ORD20251020225706714', 'q', '', 'E-Wallet', 21999.78, '2025-10-20 22:58:31'),
(2, '#ORD20251020230355454', 'aa', '', 'Kartu Kredit', 10800.00, '2025-10-20 23:04:08'),
(3, '#ORD20251020230412640', 'a', '', 'E-Wallet', 10800.00, '2025-10-20 23:04:20'),
(4, '#ORD20251110125329134', 'Eka', '', 'Kartu Debit', 10800.00, '2025-11-10 12:54:08'),
(5, '#ORD20251110125459811', 'Eka', '', 'Kartu Debit', 10800.00, '2025-11-10 12:55:13'),
(6, '#ORD20251110125536825', 'Eka', '', 'Kartu Debit', 10800.00, '2025-11-10 12:56:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`) VALUES
(1, 'MochamadEkaMiharja', 'Eka', '888', 'admin'),
(2, 'RaniaAdilla', 'Rania', '111', 'kasir');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
