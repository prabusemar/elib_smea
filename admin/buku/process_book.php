<?php
session_start();
include '../../config.php';

$action = $_GET['action'] ?? '';

if ($action === 'add' || $action === 'update') {
    // Handle add/update book
    $bukuId = $_POST['bukuId'] ?? 0;
    $judul = $_POST['judul'];
    $isbn = $_POST['isbn'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $bahasa = $_POST['bahasa'];
    $halaman = $_POST['halaman'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];

    // Handle file uploads
    $coverPath = '';
    if (!empty($_FILES['cover']['name'])) {
        $coverDir = '../../uploads/covers/';
        $coverExt = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $coverName = 'cover_' . time() . '.' . $coverExt;
        move_uploaded_file($_FILES['cover']['tmp_name'], $coverDir . $coverName);
        $coverPath = 'uploads/covers/' . $coverName;
    }

    $filePath = '';
    if (!empty($_FILES['fileEbook']['name'])) {
        $fileDir = '../../uploads/ebooks/';
        $fileExt = pathinfo($_FILES['fileEbook']['name'], PATHINFO_EXTENSION);
        $fileName = 'ebook_' . time() . '.' . $fileExt;
        move_uploaded_file($_FILES['fileEbook']['tmp_name'], $fileDir . $fileName);
        $filePath = 'uploads/ebooks/' . $fileName;
    }

    if ($action === 'add') {
        $query = "INSERT INTO buku (Judul, Penulis, Penerbit, TahunTerbit, ISBN, KategoriID, Cover, FileEbook, 
                  Deskripsi, JumlahHalaman, Bahasa, Status, TanggalUpload) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssssssss',
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $isbn,
            $kategori,
            $coverPath,
            $filePath,
            $deskripsi,
            $halaman,
            $bahasa,
            $status
        );
    } else {
        // For update, keep existing files if not uploaded new ones
        if (empty($coverPath)) {
            $coverPath = $_POST['existingCover'] ?? '';
        }
        if (empty($filePath)) {
            $filePath = $_POST['existingFile'] ?? '';
        }

        $query = "UPDATE buku SET Judul=?, Penulis=?, Penerbit=?, TahunTerbit=?, ISBN=?, KategoriID=?, 
                  Cover=?, FileEbook=?, Deskripsi=?, JumlahHalaman=?, Bahasa=?, Status=? 
                  WHERE BukuID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssssssssi',
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $isbn,
            $kategori,
            $coverPath,
            $filePath,
            $deskripsi,
            $halaman,
            $bahasa,
            $status,
            $bukuId
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Buku berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan buku']);
    }
} elseif ($action === 'delete') {
    // Handle delete book
    $id = $_POST['id'];
    $query = "DELETE FROM buku WHERE BukuID=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Buku berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus buku']);
    }
}
