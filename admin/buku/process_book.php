<?php
session_start();
include '../../config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header JSON
header('Content-Type: application/json');

// Function to handle response
function jsonResponse($success, $message, $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        // Validate required fields
        $required = ['judul', 'isbn', 'penulis', 'penerbit', 'tahun', 'kategori', 'bahasa', 'halaman', 'status'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} harus diisi!");
            }
        }

        // Prepare data
        $bukuId = $_POST['bukuId'] ?? 0;
        $judul = trim($_POST['judul']);
        $isbn = trim($_POST['isbn']);
        $penulis = trim($_POST['penulis']);
        $penerbit = trim($_POST['penerbit']);
        $tahun = (int)$_POST['tahun'];
        $kategori = (int)$_POST['kategori'];
        $bahasa = trim($_POST['bahasa']);
        $halaman = (int)$_POST['halaman'];
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = trim($_POST['status']);

        // Validate status
        $allowedStatus = ['Tersedia', 'Dipinjam'];
        if (!in_array($status, $allowedStatus)) {
            throw new Exception("Status harus 'Tersedia' atau 'Dipinjam'");
        }

        // Handle file uploads
        $coverPath = $_POST['existingCover'] ?? '';
        $filePath = $_POST['existingFile'] ?? '';

        // Create upload directories if not exists
        $coverDir = '../../uploads/covers/';
        $fileDir = '../../uploads/ebooks/';

        if (!file_exists($coverDir)) {
            mkdir($coverDir, 0777, true);
        }

        if (!file_exists($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        // Process cover upload
        if (!empty($_FILES['cover']['name'])) {
            $coverExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $allowedCoverExt = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($coverExt, $allowedCoverExt)) {
                throw new Exception("Format cover tidak didukung. Gunakan JPG, PNG, atau GIF.");
            }

            $coverName = 'cover_' . time() . '.' . $coverExt;
            $coverTmp = $_FILES['cover']['tmp_name'];

            if (!move_uploaded_file($coverTmp, $coverDir . $coverName)) {
                throw new Exception("Gagal mengunggah cover buku");
            }

            // Delete old cover if exists
            if (!empty($coverPath) && file_exists('../../' . parse_url($coverPath, PHP_URL_PATH))) {
                unlink('../../' . parse_url($coverPath, PHP_URL_PATH));
            }

            $coverPath = '/uploads/covers/' . $coverName;
        }

        // Process ebook file upload
        if (!empty($_FILES['fileEbook']['name'])) {
            $fileExt = strtolower(pathinfo($_FILES['fileEbook']['name'], PATHINFO_EXTENSION));
            $allowedFileExt = ['pdf', 'epub', 'mobi'];

            if (!in_array($fileExt, $allowedFileExt)) {
                throw new Exception("Format file tidak didukung. Gunakan PDF, EPUB, atau MOBI.");
            }

            $fileName = 'ebook_' . time() . '.' . $fileExt;
            $fileTmp = $_FILES['fileEbook']['tmp_name'];

            if (!move_uploaded_file($fileTmp, $fileDir . $fileName)) {
                throw new Exception("Gagal mengunggah file ebook");
            }

            // Delete old file if exists
            if (!empty($filePath) && file_exists('../../' . parse_url($filePath, PHP_URL_PATH))) {
                unlink('../../' . parse_url($filePath, PHP_URL_PATH));
            }

            $filePath = BASE_URL . '/uploads/ebooks/' . $fileName;
        }

        // Validate ebook file for new book
        if ($action === 'add' && empty($filePath)) {
            throw new Exception("File ebook harus diunggah");
        }

        if ($action === 'add') {
            $query = "INSERT INTO buku (Judul, Penulis, Penerbit, TahunTerbit, ISBN, KategoriID, Cover, FileEbook, Deskripsi, JumlahHalaman, Bahasa, Status, TanggalUpload) 
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
            jsonResponse(true, 'Buku berhasil disimpan', [
                'id' => $action === 'add' ? mysqli_insert_id($conn) : $bukuId
            ]);
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];

        // Get book data first
        $query = "SELECT Cover, FileEbook FROM buku WHERE BukuID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $book = mysqli_fetch_assoc($result);

        // Delete files
        if ($book) {
            if (!empty($book['Cover'])) {
                $coverPath = '../../' . parse_url($book['Cover'], PHP_URL_PATH);
                if (file_exists($coverPath)) {
                    unlink($coverPath);
                }
            }

            if (!empty($book['FileEbook'])) {
                $filePath = '../../' . parse_url($book['FileEbook'], PHP_URL_PATH);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // Delete from database
        $query = "DELETE FROM buku WHERE BukuID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Buku berhasil dihapus');
        } else {
            throw new Exception("Gagal menghapus buku: " . mysqli_error($conn));
        }
    } else {
        throw new Exception("Aksi tidak valid");
    }
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

// Di process_book.php
if (!move_uploaded_file($coverTmp, $coverDir . $coverName)) {
    error_log("Failed to move uploaded file from $coverTmp to " . $coverDir . $coverName);
    throw new Exception("Gagal mengunggah cover buku");
}
