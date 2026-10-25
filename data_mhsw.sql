-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 12:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nilai_mhsw`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_mhsw`
--

CREATE TABLE `data_mhsw` (
  `nim` char(10) NOT NULL,
  `nama_mhsw` varchar(30) NOT NULL,
  `nilai_akhir` float NOT NULL,
  `nilai_hrf` char(5) DEFAULT NULL,
  `predikat` varchar(20) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_mhsw`
--

INSERT INTO `data_mhsw` (`nim`, `nama_mhsw`, `nilai_akhir`, `nilai_hrf`, `predikat`, `foto`, `created_at`, `updated_at`) VALUES
('3202416025', 'Achmad Ardian Mulyadi', 99, 'A', 'Sangat Memuaskan', '1792862039_IMG-20250630-WA0109.jpg', '2026-10-26 05:40:08', '2026-10-26 06:21:30'),
('3202426099', 'Iman', 88, 'A', 'Sangat Memuaskan', NULL, '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('3202426101', 'Asep Septian', 57, 'C', 'Cukup', '', '2026-10-26 06:29:54', '2026-10-26 06:29:54'),
('3202426999', 'Supri', 21.61, 'E', 'Tidak Lulus', NULL, '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('320416111', 'Siswanto', 89, 'A', 'Sangat Memuaskan', '1792967431_IMG-20250110-WA0023.jpg', '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('A420241602', 'Dio Mande', 45, 'D', 'Kurang', '1792967623_IMG-20250109-WA0055.jpg', '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('F320242619', 'Zidan', 21, 'E', 'Tidak Lulus', '1792967342_IMG-20241124-WA0020.jpg', '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('G320241602', 'Charles Krisna Dharma Jati', 88, 'A', 'Sangat Memuaskan', '1792967170_IMG-20250102-WA0340.jpg', '2026-10-26 05:40:08', '2026-10-26 05:40:08'),
('H202226109', 'Mbappe', 67, 'B', 'Memuaskan', '', '2026-10-26 05:40:08', '2026-10-26 05:40:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_mhsw`
--
ALTER TABLE `data_mhsw`
  ADD PRIMARY KEY (`nim`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
