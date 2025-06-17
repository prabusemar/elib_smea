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

    // Validasi sederhana
    if (!$member_id || !$buku_id || !$tanggal_pinjam || !$tanggal_kembali) {
        $_SESSION['error'] = 'Semua field wajib diisi!';
        header('Location: tambah_peminjaman.php');
        exit;
    }

    // Insert ke tabel peminjaman
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
        $_SESSION['error'] = 'Gagal menambah peminjaman.';
    }
    header('Location: tambah_peminjaman.php');
    exit;
}
// Jika bukan POST, redirect
header('Location: tambah_peminjaman.php');
exit;
