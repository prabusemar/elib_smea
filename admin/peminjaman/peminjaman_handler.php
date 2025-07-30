<?php
// c:/laragon/www/library/admin/peminjaman/peminjaman_handler.php

session_start();
require_once '../../config.php';

// Hanya admin yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $member_id = (int)$_POST['member_id'];
    $buku_id = (int)$_POST['buku_id'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];

    // Validasi dasar
    if (!$member_id || !$buku_id || !$tanggal_pinjam || !$tanggal_kembali) {
        $_SESSION['error'] = 'Semua field wajib diisi!';
        header('Location: tambah_peminjaman.php');
        exit;
    }

    // 1. Cek JenisAkun dan jumlah peminjaman aktif
    $sql_check = "SELECT JenisAkun, Status FROM anggota WHERE MemberID = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, 'i', $member_id);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $anggota = mysqli_fetch_assoc($result);

    if (!$anggota) {
        $_SESSION['error'] = 'Anggota tidak ditemukan.';
        header('Location: tambah_peminjaman.php');
        exit;
    }

    $jenis_akun = $anggota['JenisAkun'];

    // 2. Hitung jumlah peminjaman aktif
    $sql_count = "SELECT COUNT(*) AS total FROM peminjaman 
              WHERE MemberID = ? AND (Status = 'Active' OR Status = 'Expired')";
    $stmt_count = mysqli_prepare($conn, $sql_count);
    mysqli_stmt_bind_param($stmt_count, 'i', $member_id);
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $count = mysqli_fetch_assoc($result_count);
    $total_active = $count['total'];
    // 3. Batasi jika akun Free dan sudah pinjam 5
    if ($jenis_akun === 'Free' && $total_active >= 5) {
        $_SESSION['error'] = 'Anggota dengan akun Free hanya dapat meminjam maksimal 5 buku.';
        header('Location: tambah_peminjaman.php');
        exit;
    }

    // 4. Insert peminjaman
    $sql = "INSERT INTO peminjaman (MemberID, BukuID, TanggalPinjam, TanggalKembali, Status) VALUES (?, ?, ?, ?, 'Active')";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'iiss', $member_id, $buku_id, $tanggal_pinjam, $tanggal_kembali);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Peminjaman berhasil ditambahkan!';
            header('Location: peminjaman_admin.php');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menambah peminjaman.';
        }
    } else {
        $_SESSION['error'] = 'Gagal memproses permintaan.';
    }

    header('Location: tambah_peminjaman.php');
    exit;
}

// Jika bukan POST, redirect
header('Location: tambah_peminjaman.php');
exit;
