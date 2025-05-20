-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 20, 2025 at 05:39 AM
-- Server version: 5.7.39
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `MemberID` int(11) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FotoProfil` varchar(255) DEFAULT 'default.jpg',
  `TanggalBergabung` date NOT NULL,
  `Status` enum('Active','Suspended','Banned') DEFAULT 'Active',
  `MasaBerlaku` date DEFAULT NULL COMMENT 'Tanggal expire membership',
  `JenisAkun` enum('Free','Premium') DEFAULT 'Free',
  `TerakhirLogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`MemberID`, `Nama`, `Email`, `Password`, `FotoProfil`, `TanggalBergabung`, `Status`, `MasaBerlaku`, `JenisAkun`, `TerakhirLogin`) VALUES
(1, 'Musa Pribadi Alfaruq', 'musaalfaruq@gmail.com', '$2y$10$Dxys74PkWgM17RA4y2OJnOk723jmugJUIC2Q4Ri271hO0v6HAjP.2', 'default.jpg', '2025-05-05', 'Active', '2026-12-02', 'Premium', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `anggota_grup`
--

CREATE TABLE `anggota_grup` (
  `GrupID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `TanggalBergabung` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `anotasi`
--

CREATE TABLE `anotasi` (
  `AnotasiID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `TeksTerseleksi` text NOT NULL,
  `Halaman` int(11) NOT NULL,
  `Catatan` text,
  `Warna` varchar(20) DEFAULT '#FFFF00',
  `DibuatPada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `BookmarkID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `Halaman` int(11) NOT NULL,
  `Catatan` text,
  `WarnaMarker` varchar(20) DEFAULT '#FFFF00',
  `DibuatPada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `BukuID` int(11) NOT NULL,
  `Judul` varchar(255) NOT NULL,
  `Penulis` varchar(100) NOT NULL,
  `Penerbit` varchar(100) DEFAULT NULL,
  `TahunTerbit` year(4) DEFAULT NULL,
  `ISBN` varchar(20) DEFAULT NULL,
  `KategoriID` int(11) DEFAULT NULL,
  `Cover` varchar(255) DEFAULT NULL COMMENT 'URL/path gambar cover',
  `DriveURL` varchar(255) DEFAULT NULL COMMENT 'URL Google Drive untuk embed ebook',
  `Deskripsi` text,
  `Bahasa` varchar(20) NOT NULL DEFAULT 'Indonesia',
  `JumlahHalaman` int(11) DEFAULT NULL,
  `FormatEbook` varchar(10) NOT NULL DEFAULT 'PDF',
  `UkuranFile` bigint(20) DEFAULT NULL COMMENT 'Dalam bytes',
  `TanggalUpload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Rating` decimal(3,1) DEFAULT '0.0',
  `JumlahBaca` int(11) DEFAULT '0',
  `Status` enum('Free','Premium') NOT NULL DEFAULT 'Free',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DeletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`BukuID`, `Judul`, `Penulis`, `Penerbit`, `TahunTerbit`, `ISBN`, `KategoriID`, `Cover`, `DriveURL`, `Deskripsi`, `Bahasa`, `JumlahHalaman`, `FormatEbook`, `UkuranFile`, `TanggalUpload`, `Rating`, `JumlahBaca`, `Status`, `CreatedAt`, `UpdatedAt`, `DeletedAt`) VALUES
(18, 'A Conjuring of Light', 'V.E. Schwab', 'Tor Books', 2017, '0765387476', 9, 'uploads/covers/682a1623ad717.jpg', 'https://drive.google.com/file/d/1Ncau7i9vhfzYiCXKz_KwYjM2Y8LJ3fQy/view?usp=drive_link', 'A Conjuring of Light picks up directly where A Gathering of Shadows left off. The precarious balance between the four Londons is breaking. Darkness is spreading through the Maresh Empire in Red London, threatening to consume its magic.', 'Indonesia', 766, 'PDF', 3, '2025-05-18 17:16:20', '0.0', 0, 'Free', '2025-05-19 00:16:20', '2025-05-19 21:36:16', NULL),
(19, 'Twilight', 'Stephenie Meyer', 'PT Gramedia Pustaka Utama', 2009, '9786020327389', 9, 'uploads/covers/682c0dfc766fb.jpg', 'https://drive.google.com/file/d/1McaBmzTtVxRHkFwKb5bqIiDVsNG6QAtS/view?usp=drive_link', 'Bella Swan, seorang gadis remaja penyendiri dan canggung, pindah dari Phoenix yang cerah ke kota Forks yang selalu hujan untuk tinggal bersama ayahnya. Ia tidak berharap banyak dari kehidupan barunya, namun semuanya berubah ketika ia bertemu dengan Edward Cullen, seorang siswa laki-laki yang misterius dan mempesona.\\r\\n\\r\\nKetertarikan Bella pada Edward begitu kuat dan tak tertahankan, meskipun ia merasakan ada sesuatu yang berbeda dan tersembunyi darinya. Seiring berjalannya waktu, Bella mengungkap rahasia gelap Edward: ia adalah seorang vampir.\\r\\n\\r\\nMeskipun menyadari bahaya yang mengintai, Bella tidak dapat menjauhi Edward. Mereka terjerat dalam hubungan yang penuh gairah namun juga berbahaya, di mana cinta dan ancaman kematian berjalan berdampingan. Bella harus menghadapi dunia supernatural yang selama ini tidak pernah ia bayangkan, termasuk konfrontasi dengan vampir lain yang mengancam nyawanya dan orang-orang yang ia cintai.\\r\\n\\r\\nTwilight adalah kisah tentang cinta pertama yang intens, pengorbanan, dan perjuangan antara keinginan dan kewajiban di tengah dunia fantasi yang gelap dan memikat. Novel ini mengeksplorasi tema-tema universal tentang identitas, keluarga, dan pilihan yang membentuk hidup seseorang.', 'Indonesia', 524, 'PDF', 3, '2025-05-20 05:07:08', '0.0', 0, 'Free', '2025-05-20 12:07:08', '2025-05-20 12:07:08', NULL),
(20, 'New Moon', 'Stephenie Meyer', 'PT Gramedia Pustaka Utama', 2009, '9786020327396', 9, 'uploads/covers/682c10da3ef4a.jpg', 'https://drive.google.com/file/d/1BL8gGEi_1YJuw2HpAhPIEExj3ddbx3AG/view?usp=drive_link', 'Setelah mengalami cinta yang intens namun berbahaya dengan vampir Edward Cullen, Bella Swan kembali menghadapi kehampaan yang mendalam ketika Edward dan keluarganya tiba-tiba meninggalkan Forks. Patah hati dan merasa kehilangan separuh jiwanya, Bella menarik diri dari semua orang dan terperangkap dalam kesedihan yang melumpuhkan.\\r\\n\\r\\nDalam kesendiriannya, Bella menemukan kenyamanan yang tak terduga dalam persahabatannya dengan Jacob Black, seorang pemuda suku Quileute yang penuh semangat dan menyimpan rahasianya sendiri. Seiring berjalannya waktu, ikatan antara Bella dan Jacob semakin kuat, membantunya perlahan-lahan bangkit dari keterpurukan.\\r\\n\\r\\nNamun, dunia supernatural tidak pernah benar-benar meninggalkan Bella. Ketika ia secara tidak sengaja menemukan bahwa melakukan tindakan berbahaya dapat memunculkan \\\"hantu\\\" Edward, ia mulai mencari sensasi yang mengancam nyawanya. Di tengah kebingungannya, Bella terjebak di antara dua cinta yang berbeda namun sama kuatnya: cintanya pada Edward yang abadi namun penuh bahaya, dan perasaannya yang tumbuh untuk Jacob yang hangat dan nyata.\\r\\n\\r\\nLebih jauh lagi, Bella menyadari bahwa Jacob dan kaumnya menyimpan rahasia kuno dan kuat, yang membawanya lebih dalam ke dalam dunia makhluk supernatural yang penuh dengan persaingan dan ancaman baru. New Moon adalah kisah tentang kehilangan, penyembuhan, persahabatan, dan cinta segitiga yang rumit di tengah bayang-bayang dunia vampir dan manusia serigala.', 'Indonesia', 603, 'PDF', 2, '2025-05-20 05:19:22', '0.0', 0, 'Free', '2025-05-20 12:19:22', '2025-05-20 12:19:22', NULL),
(21, 'Eclipse', 'Stephenie Meyer', 'PT Gramedia Pustaka Utama', 2010, '9786020327402', 9, 'uploads/covers/682c12535d752.jpg', 'https://drive.google.com/file/d/135_efQZ5PVsml5IzXRVnWN_qb_Zl_VUS/view?usp=drive_link', 'Bella Swan kembali dihadapkan pada pilihan yang sulit. Meskipun cintanya pada vampir Edward Cullen semakin dalam, ia juga merasakan ikatan yang kuat dengan sahabatnya, manusia serigala Jacob Black. Kehadiran Jacob semakin rumit karena Bella tahu bahwa dunia vampir dan manusia serigala adalah musuh bebuyutan.\\r\\n\\r\\nKetegangan di Forks meningkat dengan munculnya vampir \\\"baru lahir\\\" yang misterius dan haus darah. Kekuatan mereka jauh lebih besar dan mereka menimbulkan ancaman serius bagi Bella dan keluarga Cullen. Untuk melindungi Bella, keluarga Cullen dan kawanan serigala Quileute harus mengesampingkan permusuhan mereka dan bekerja sama.\\r\\n\\r\\nDi tengah persiapan pertempuran yang menegangkan, Bella harus membuat keputusan akhir antara cintanya pada Edward dan persahabatannya dengan Jacob. Ia menyadari bahwa keputusannya tidak hanya akan memengaruhi hatinya, tetapi juga masa depan kedua komunitas supernatural tersebut.\\r\\n\\r\\nEclipse adalah kisah tentang pengorbanan, kesetiaan, dan pilihan yang menentukan di tengah konflik yang semakin memanas. Bella harus menghadapi konsekuensi dari perasaannya dan memahami arti sebenarnya dari cinta dan pengorbanan demi orang-orang yang dicintainya. Ancaman eksternal memaksa karakter-karakter untuk tumbuh dan membuat aliansi yang tidak terduga.', 'Indonesia', 692, 'PDF', 2, '2025-05-20 05:25:39', '0.0', 0, 'Premium', '2025-05-20 12:25:39', '2025-05-20 12:25:39', NULL),
(22, 'Breaking Dawn', 'Stephenie Meyer', 'PT Gramedia Pustaka Utama', 2011, '9786020327419', 9, 'uploads/covers/682c1403bce1f.jpg', 'https://drive.google.com/file/d/1mkx6L-HrJzgK3QiUB7V0vn3lpJuzqy8Q/view?usp=drive_link', 'Setelah melalui berbagai rintangan dan pilihan yang sulit, Bella Swan akhirnya menikah dengan cinta sejatinya, Edward Cullen. Pernikahan impian mereka diikuti dengan bulan madu romantis yang tak terlupakan. Namun, kebahagiaan mereka segera diwarnai dengan kejadian yang tak terduga: Bella hamil.\\r\\n\\r\\nKehamilan Bella membawa konsekuensi yang berbahaya dan mengancam nyawanya, karena bayi yang dikandungnya adalah setengah vampir dan tumbuh dengan kecepatan yang luar biasa. Keluarga Cullen berusaha sekuat tenaga untuk menjaga Bella tetap hidup hingga kelahiran bayinya.\\r\\n\\r\\nKelahiran Renesmee, putri Bella dan Edward, membawa kebahagiaan sekaligus masalah baru. Keberadaan Renesmee, seorang anak setengah manusia dan setengah vampir, menjadi ancaman bagi keberadaan komunitas vampir karena melanggar hukum kuno mereka. Kabar tentang Renesmee sampai ke telinga Volturi, para pemimpin dunia vampir yang sangat berkuasa, dan mereka menganggapnya sebagai ancaman yang harus dieliminasi.\\r\\n\\r\\nUntuk melindungi putri mereka, keluarga Cullen mengumpulkan sekutu dari seluruh dunia vampir untuk bersaksi tentang keberadaan Renesmee yang tidak berbahaya. Bella, yang kini juga telah menjadi vampir, harus menggunakan kekuatan barunya untuk melindungi keluarganya dari ancaman yang datang.\\r\\n\\r\\nBreaking Dawn adalah puncak dari kisah cinta Bella dan Edward, yang diwarnai dengan pernikahan, kelahiran, pengorbanan, dan pertempuran terakhir untuk melindungi keluarga mereka. Buku ini mengeksplorasi tema tentang keluarga, identitas, dan batas-batas cinta yang abadi.', 'Indonesia', 866, 'PDF', 6, '2025-05-20 05:32:51', '0.0', 0, 'Premium', '2025-05-20 12:32:51', '2025-05-20 12:32:51', NULL),
(23, 'Midnight Sun', 'Stephenie Meyer', 'PT Gramedia Pustaka Utama', 2020, '9786020648683', 9, 'uploads/covers/682c152365708.jpg', 'https://drive.google.com/file/d/1H61fKJbyycS8hx3QZf9GBEhXDwH5-6vi/view?usp=drive_link', 'Untuk pertama kalinya, pembaca dapat mengalami kisah Twilight yang ikonik melalui mata Edward Cullen. Diceritakan dari sudut pandangnya, Midnight Sun membawa perspektif baru yang gelap dan penuh kejutan pada kisah cinta antara Bella dan Edward.\\r\\n\\r\\nMelalui pikiran Edward, kita dibawa masuk ke dalam benaknya yang kompleks, di mana ia bergumul dengan daya tarik yang tak tertahankan terhadap Bella, sekaligus menyadari bahaya yang ditimbulkannya bagi gadis manusia itu. Kita menyaksikan kecemasannya yang mendalam, konflik internalnya sebagai vampir yang berusaha menahan diri, dan pemahamannya yang semakin dalam tentang jiwa Bella yang unik dan memikat.\\r\\n\\r\\nMidnight Sun memberikan wawasan yang lebih kaya tentang pertemuan pertama mereka, perkembangan hubungan mereka, dan tantangan-tantangan yang mereka hadapi dari sudut pandang Edward. Pembaca akan mendapatkan pemahaman yang lebih mendalam tentang masa lalu Edward, pemikirannya, dan perasaannya yang sebenarnya terhadap Bella, keluarganya, dan dunia vampir.\\r\\n\\r\\nBuku ini menawarkan pengalaman yang segar dan menarik bagi para penggemar Twilight, memungkinkan mereka untuk menghidupkan kembali kisah yang mereka cintai dari perspektif karakter pria misterius yang selama ini menyimpan banyak rahasia.', 'Indonesia', 1019, 'PDF', 3, '2025-05-20 05:37:39', '0.0', 0, 'Premium', '2025-05-20 12:37:39', '2025-05-20 12:37:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `diskusi_buku`
--

CREATE TABLE `diskusi_buku` (
  `DiskusiID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Konten` text NOT NULL,
  `DibuatPada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ParentID` int(11) DEFAULT NULL COMMENT 'Untuk reply'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ebook_metadata`
--

CREATE TABLE `ebook_metadata` (
  `MetadataID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `DOI` varchar(50) DEFAULT NULL,
  `Edisi` varchar(20) DEFAULT NULL,
  `ISBN13` varchar(15) DEFAULT NULL,
  `BahasaAsli` varchar(50) DEFAULT NULL,
  `Penerjemah` varchar(100) DEFAULT NULL,
  `HakCipta` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `favorit`
--

CREATE TABLE `favorit` (
  `FavoritID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `TanggalDitambahkan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `grup_membaca`
--

CREATE TABLE `grup_membaca` (
  `GrupID` int(11) NOT NULL,
  `NamaGrup` varchar(100) NOT NULL,
  `Deskripsi` text,
  `GambarCover` varchar(255) DEFAULT NULL,
  `DibuatOleh` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `KategoriID` int(11) NOT NULL,
  `NamaKategori` varchar(50) NOT NULL,
  `Deskripsi` text,
  `Icon` varchar(50) DEFAULT 'book'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`KategoriID`, `NamaKategori`, `Deskripsi`, `Icon`) VALUES
(7, 'Horror', 'Kumpulan ebook horror terbaik', '6816cf50ce693.png'),
(8, 'Sains', 'Kumpulan ebook ilmu pengetahuan', '6816d476091f2.png'),
(9, 'Fiksi', 'Kumpulan buku berkategori fiksi terbaik', '6825faf9da536.png');

-- --------------------------------------------------------

--
-- Table structure for table `koleksi`
--

CREATE TABLE `koleksi` (
  `KoleksiID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `JudulKoleksi` varchar(100) NOT NULL,
  `Deskripsi` text,
  `IsPublic` tinyint(1) DEFAULT '0',
  `DibuatPada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `koleksi_buku`
--

CREATE TABLE `koleksi_buku` (
  `KoleksiBukuID` int(11) NOT NULL,
  `KoleksiID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `DitambahkanPada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `NotifikasiID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Judul` varchar(100) NOT NULL,
  `Pesan` text NOT NULL,
  `Tipe` enum('System','DueDate','NewBook','Promo') DEFAULT NULL,
  `Dibaca` tinyint(1) DEFAULT '0',
  `Waktu` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `PembayaranID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Jumlah` decimal(10,2) NOT NULL,
  `Metode` enum('Transfer','CreditCard','E-Wallet') DEFAULT NULL,
  `Status` enum('Pending','Completed','Failed') DEFAULT NULL,
  `TanggalPembayaran` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `MasaAktif` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `PeminjamanID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `TanggalPinjam` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `TanggalKembali` timestamp NULL DEFAULT NULL,
  `DurasiPinjam` int(11) DEFAULT '14' COMMENT 'Durasi dalam hari',
  `Status` enum('Active','Expired','Returned') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pencarian_populer`
--

CREATE TABLE `pencarian_populer` (
  `PencarianID` int(11) NOT NULL,
  `KataKunci` varchar(100) NOT NULL,
  `Jumlah` int(11) DEFAULT '1',
  `TerakhirDicari` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi`
--

CREATE TABLE `rekomendasi` (
  `RekomendasiID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `Sumber` enum('System','Editor','AI') NOT NULL,
  `Alasan` text,
  `Prioritas` int(11) DEFAULT '0',
  `PeriodeMulai` date DEFAULT NULL,
  `PeriodeSelesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_baca`
--

CREATE TABLE `riwayat_baca` (
  `RiwayatID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `HalamanTerakhir` int(11) DEFAULT '1',
  `Progress` decimal(5,2) DEFAULT '0.00' COMMENT 'Persentase',
  `TerakhirBaca` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `WaktuBaca` int(11) DEFAULT '0' COMMENT 'Total menit membaca'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sinkronisasi`
--

CREATE TABLE `sinkronisasi` (
  `SinkronID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `DeviceID` varchar(255) NOT NULL,
  `DeviceName` varchar(100) DEFAULT NULL,
  `TerakhirSync` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Progress` text COMMENT 'JSON data progress baca'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `statistik_membaca`
--

CREATE TABLE `statistik_membaca` (
  `StatistikID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `Sesi` date NOT NULL,
  `MenitBaca` int(11) NOT NULL,
  `HalamanDibaca` int(11) NOT NULL,
  `Kecepatan` decimal(5,2) DEFAULT NULL COMMENT 'halaman per jam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `UlasanID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `BukuID` int(11) NOT NULL,
  `Rating` decimal(2,1) NOT NULL,
  `Komentar` text,
  `TanggalUlas` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Likes` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','member') DEFAULT 'member',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'pribadi', '1sampai8', 'admin', NULL, '2025-04-29 18:27:51', '2025-04-29 18:28:11'),
(2, 'galuh', '123456789', 'staff', NULL, '2025-04-29 18:27:51', '2025-04-29 18:28:17'),
(3, 'prabusemar', '$2y$10$bcgB/Daenrjdc0Rv79xbKecBEUL0Pw6V.R5vlEOXAa2.QdcqKyNqa', 'admin', NULL, '2025-04-29 18:27:51', '2025-04-29 18:28:25'),
(4, 'musaalfaruq@gmail.com', '$2y$10$Dxys74PkWgM17RA4y2OJnOk723jmugJUIC2Q4Ri271hO0v6HAjP.2', 'member', NULL, '2025-05-04 17:07:42', '2025-05-04 17:07:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `anggota_grup`
--
ALTER TABLE `anggota_grup`
  ADD PRIMARY KEY (`GrupID`,`MemberID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `anotasi`
--
ALTER TABLE `anotasi`
  ADD PRIMARY KEY (`AnotasiID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`BookmarkID`),
  ADD KEY `BukuID` (`BukuID`),
  ADD KEY `idx_member_book` (`MemberID`,`BukuID`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`BukuID`),
  ADD UNIQUE KEY `ISBN` (`ISBN`),
  ADD KEY `KategoriID` (`KategoriID`);

--
-- Indexes for table `diskusi_buku`
--
ALTER TABLE `diskusi_buku`
  ADD PRIMARY KEY (`DiskusiID`),
  ADD KEY `BukuID` (`BukuID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `ParentID` (`ParentID`);

--
-- Indexes for table `ebook_metadata`
--
ALTER TABLE `ebook_metadata`
  ADD PRIMARY KEY (`MetadataID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `favorit`
--
ALTER TABLE `favorit`
  ADD PRIMARY KEY (`FavoritID`),
  ADD UNIQUE KEY `uniq_favorite` (`MemberID`,`BukuID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `grup_membaca`
--
ALTER TABLE `grup_membaca`
  ADD PRIMARY KEY (`GrupID`),
  ADD KEY `DibuatOleh` (`DibuatOleh`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`KategoriID`),
  ADD UNIQUE KEY `NamaKategori` (`NamaKategori`);

--
-- Indexes for table `koleksi`
--
ALTER TABLE `koleksi`
  ADD PRIMARY KEY (`KoleksiID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `koleksi_buku`
--
ALTER TABLE `koleksi_buku`
  ADD PRIMARY KEY (`KoleksiBukuID`),
  ADD UNIQUE KEY `uniq_collection_book` (`KoleksiID`,`BukuID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`NotifikasiID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`PembayaranID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`PeminjamanID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `BukuID` (`BukuID`),
  ADD KEY `idx_status` (`Status`);

--
-- Indexes for table `pencarian_populer`
--
ALTER TABLE `pencarian_populer`
  ADD PRIMARY KEY (`PencarianID`),
  ADD KEY `idx_keyword` (`KataKunci`);

--
-- Indexes for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD PRIMARY KEY (`RekomendasiID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `riwayat_baca`
--
ALTER TABLE `riwayat_baca`
  ADD PRIMARY KEY (`RiwayatID`),
  ADD UNIQUE KEY `uniq_reading` (`MemberID`,`BukuID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `sinkronisasi`
--
ALTER TABLE `sinkronisasi`
  ADD PRIMARY KEY (`SinkronID`),
  ADD UNIQUE KEY `uniq_device` (`MemberID`,`DeviceID`);

--
-- Indexes for table `statistik_membaca`
--
ALTER TABLE `statistik_membaca`
  ADD PRIMARY KEY (`StatistikID`),
  ADD KEY `BukuID` (`BukuID`),
  ADD KEY `idx_reading_session` (`MemberID`,`Sesi`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`UlasanID`),
  ADD UNIQUE KEY `uniq_review` (`MemberID`,`BukuID`),
  ADD KEY `BukuID` (`BukuID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `anotasi`
--
ALTER TABLE `anotasi`
  MODIFY `AnotasiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `BookmarkID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `BukuID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `diskusi_buku`
--
ALTER TABLE `diskusi_buku`
  MODIFY `DiskusiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ebook_metadata`
--
ALTER TABLE `ebook_metadata`
  MODIFY `MetadataID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorit`
--
ALTER TABLE `favorit`
  MODIFY `FavoritID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grup_membaca`
--
ALTER TABLE `grup_membaca`
  MODIFY `GrupID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `KategoriID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `koleksi`
--
ALTER TABLE `koleksi`
  MODIFY `KoleksiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `koleksi_buku`
--
ALTER TABLE `koleksi_buku`
  MODIFY `KoleksiBukuID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `NotifikasiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `PembayaranID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `PeminjamanID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pencarian_populer`
--
ALTER TABLE `pencarian_populer`
  MODIFY `PencarianID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  MODIFY `RekomendasiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_baca`
--
ALTER TABLE `riwayat_baca`
  MODIFY `RiwayatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sinkronisasi`
--
ALTER TABLE `sinkronisasi`
  MODIFY `SinkronID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistik_membaca`
--
ALTER TABLE `statistik_membaca`
  MODIFY `StatistikID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `UlasanID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anotasi`
--
ALTER TABLE `anotasi`
  ADD CONSTRAINT `anotasi_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `anotasi_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `bookmark_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `bookmark_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`KategoriID`) REFERENCES `kategori` (`KategoriID`);

--
-- Constraints for table `diskusi_buku`
--
ALTER TABLE `diskusi_buku`
  ADD CONSTRAINT `diskusi_buku_ibfk_1` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`),
  ADD CONSTRAINT `diskusi_buku_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `diskusi_buku_ibfk_3` FOREIGN KEY (`ParentID`) REFERENCES `diskusi_buku` (`DiskusiID`);

--
-- Constraints for table `ebook_metadata`
--
ALTER TABLE `ebook_metadata`
  ADD CONSTRAINT `ebook_metadata_ibfk_1` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `favorit`
--
ALTER TABLE `favorit`
  ADD CONSTRAINT `favorit_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `favorit_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `grup_membaca`
--
ALTER TABLE `grup_membaca`
  ADD CONSTRAINT `grup_membaca_ibfk_1` FOREIGN KEY (`DibuatOleh`) REFERENCES `anggota` (`MemberID`);

--
-- Constraints for table `koleksi`
--
ALTER TABLE `koleksi`
  ADD CONSTRAINT `koleksi_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`);

--
-- Constraints for table `koleksi_buku`
--
ALTER TABLE `koleksi_buku`
  ADD CONSTRAINT `koleksi_buku_ibfk_1` FOREIGN KEY (`KoleksiID`) REFERENCES `koleksi` (`KoleksiID`),
  ADD CONSTRAINT `koleksi_buku_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`);

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD CONSTRAINT `rekomendasi_ibfk_1` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `riwayat_baca`
--
ALTER TABLE `riwayat_baca`
  ADD CONSTRAINT `riwayat_baca_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `riwayat_baca_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `sinkronisasi`
--
ALTER TABLE `sinkronisasi`
  ADD CONSTRAINT `sinkronisasi_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`);

--
-- Constraints for table `statistik_membaca`
--
ALTER TABLE `statistik_membaca`
  ADD CONSTRAINT `statistik_membaca_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `statistik_membaca_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `anggota` (`MemberID`),
  ADD CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`BukuID`) REFERENCES `buku` (`BukuID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
