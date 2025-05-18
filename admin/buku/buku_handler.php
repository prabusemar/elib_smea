<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah Buku
    if (isset($_POST['action']) && $_POST['action'] == 'add_book') {
        $judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
        $penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
        $penerbit = mysqli_real_escape_string($conn, trim($_POST['penerbit'] ?? ''));
        $tahun = (int)$_POST['tahun'];
        $isbn = mysqli_real_escape_string($conn, trim($_POST['isbn'] ?? ''));
        $kategori = !empty($_POST['kategori']) ? (int)$_POST['kategori'] : NULL;
        $driveurl = mysqli_real_escape_string($conn, trim($_POST['driveurl']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
        $halaman = !empty($_POST['halaman']) ? (int)$_POST['halaman'] : NULL;
        $bahasa = mysqli_real_escape_string($conn, trim($_POST['bahasa'] ?? 'Indonesia'));
        // Pastikan nilai bahasa valid
        $allowed_bahasa = ['Indonesia', 'Inggris', 'Arab', 'Lainnya'];
        $bahasa_valid = in_array($bahasa, $allowed_bahasa) ? $bahasa : 'Indonesia';
        $format = mysqli_real_escape_string($conn, trim($_POST['format'] ?? 'PDF'));
        $ukuran = !empty($_POST['ukuran']) ? (float)$_POST['ukuran'] : NULL;
        $rating = !empty($_POST['rating']) ? (float)$_POST['rating'] : 0.0;
        $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'Free'));
        $cover = '';

        // Validasi input
        if (empty($judul) || empty($penulis) || empty($tahun) || empty($status) || empty($driveurl) || empty($bahasa)) {
            $_SESSION['error'] = "Field yang wajib diisi tidak boleh kosong!";
            header("Location: buku_admin.php");
            exit;
        }

        // Validasi URL Google Drive
        if (!filter_var($driveurl, FILTER_VALIDATE_URL) || strpos($driveurl, 'drive.google.com') === false) {
            $_SESSION['error'] = "URL Google Drive tidak valid!";
            header("Location: buku_admin.php");
            exit;
        }

        // Handle file upload cover
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../../uploads/covers/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExt = strtolower(pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExt, $allowedTypes)) {
                $_SESSION['error'] = "Format file cover tidak didukung (hanya JPG, PNG, GIF)";
                header("Location: buku_admin.php");
                exit;
            }

            if ($_FILES['cover']['size'] > 2 * 1024 * 1024) { // 2MB
                $_SESSION['error'] = "Ukuran file cover terlalu besar (maks 2MB)";
                header("Location: buku_admin.php");
                exit;
            }

            $filename = uniqid() . '.' . $fileExt;
            $targetPath = $targetDir . $filename;

            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $targetPath)) {
                $cover = 'uploads/covers/' . $filename;
            } else {
                $_SESSION['error'] = "Gagal mengupload cover";
                header("Location: buku_admin.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Harap upload cover buku";
            header("Location: buku_admin.php");
            exit;
        }

        // Debug: Tampilkan nilai sebelum di-bind
        error_log("Bahasa value: " . $bahasa_valid);

        // Insert ke database
        $query = "INSERT INTO buku (
                Judul, Penulis, Penerbit, TahunTerbit, ISBN, KategoriID, 
                DriveURL, Deskripsi, JumlahHalaman, Bahasa, FormatEbook, 
                UkuranFile, Rating, Status, Cover
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "sssisisssssdiss", // Perhatikan jumlah 's' sesuai parameter
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $isbn,
            $kategori,
            $driveurl,
            $deskripsi,
            $halaman,
            $bahasa_valid,  // Gunakan yang sudah divalidasi
            $format,
            $ukuran,
            $rating,
            $status,
            $cover
        );
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Buku berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menambahkan buku: " . mysqli_error($conn);
            // Hapus file cover jika gagal insert
            if (!empty($cover)) {
                @unlink("../../" . $cover);
            }
        }

        mysqli_stmt_close($stmt);
        header("Location: buku_admin.php");
        exit;
    }
    // Edit Buku
    elseif (isset($_POST['action']) && $_POST['action'] == 'update_book') {
        $bukuID = (int)$_POST['buku_id'];
        $judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
        $penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
        $penerbit = mysqli_real_escape_string($conn, trim($_POST['penerbit'] ?? ''));
        $tahun = (int)$_POST['tahun'];
        $isbn = mysqli_real_escape_string($conn, trim($_POST['isbn'] ?? ''));
        $kategori = !empty($_POST['kategori']) ? (int)$_POST['kategori'] : NULL;
        $driveurl = mysqli_real_escape_string($conn, trim($_POST['driveurl']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
        $halaman = !empty($_POST['halaman']) ? (int)$_POST['halaman'] : NULL;
        $bahasa = mysqli_real_escape_string($conn, trim($_POST['bahasa'] ?? 'Indonesia'));
        $allowed_bahasa = ['Indonesia', 'Inggris', 'Arab', 'Lainnya'];
        $bahasa_valid = in_array($bahasa, $allowed_bahasa) ? $bahasa : 'Indonesia';
        $format = mysqli_real_escape_string($conn, trim($_POST['format'] ?? 'PDF'));
        $ukuran = !empty($_POST['ukuran']) ? (float)$_POST['ukuran'] : NULL;
        $rating = !empty($_POST['rating']) ? (float)$_POST['rating'] : 0.0;
        $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'Free'));
        $cover = mysqli_real_escape_string($conn, trim($_POST['existing_cover'] ?? ''));

        // Validasi input
        if (empty($judul) || empty($penulis) || empty($tahun) || empty($status) || empty($driveurl) || empty($bahasa)) {
            $_SESSION['error'] = "Field yang wajib diisi tidak boleh kosong!";
            header("Location: buku_admin.php");
            exit;
        }


        // Validasi URL Google Drive
        if (!filter_var($driveurl, FILTER_VALIDATE_URL) || strpos($driveurl, 'drive.google.com') === false) {
            $_SESSION['error'] = "URL Google Drive tidak valid!";
            header("Location: buku_admin.php");
            exit;
        }

        // Handle file upload jika ada cover baru
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../../uploads/covers/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExt = strtolower(pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExt, $allowedTypes)) {
                $_SESSION['error'] = "Format file cover tidak didukung (hanya JPG, PNG, GIF)";
                header("Location: buku_admin.php");
                exit;
            }

            if ($_FILES['cover']['size'] > 2 * 1024 * 1024) { // 2MB
                $_SESSION['error'] = "Ukuran file cover terlalu besar (maks 2MB)";
                header("Location: buku_admin.php");
                exit;
            }

            $filename = uniqid() . '.' . $fileExt;
            $targetPath = $targetDir . $filename;

            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $targetPath)) {
                // Hapus cover lama jika ada
                if (!empty($cover) && file_exists("../../" . $cover)) {
                    @unlink("../../" . $cover);
                }
                $cover = 'uploads/covers/' . $filename;
            } else {
                $_SESSION['error'] = "Gagal mengupload cover";
                header("Location: buku_admin.php");
                exit;
            }
        }

        // Update database
        $query = "UPDATE buku SET 
              Judul = ?, Penulis = ?, Penerbit = ?, TahunTerbit = ?, ISBN = ?, 
              KategoriID = ?, DriveURL = ?, Deskripsi = ?, JumlahHalaman = ?, 
              Bahasa = ?, FormatEbook = ?, UkuranFile = ?, Rating = ?, Status = ?, 
              Cover = ?, UpdatedAt = CURRENT_TIMESTAMP
              WHERE BukuID = ?";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "sssisisssssdissi", // Perhatikan jumlah 's' sesuai parameter
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $isbn,
            $kategori,
            $driveurl,
            $deskripsi,
            $halaman,
            $bahasa_valid,  // Gunakan yang sudah divalidasi
            $format,
            $ukuran,
            $rating,
            $status,
            $cover
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Buku berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui buku: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
        header("Location: buku_admin.php");
        exit;
    }
    // Hapus Buku (Soft Delete)
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete_book') {
        $bukuID = (int)$_POST['buku_id'];

        // Dapatkan path cover untuk dihapus
        $query = "SELECT Cover FROM buku WHERE BukuID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bukuID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $book = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Soft delete dengan mengisi DeletedAt
        $query = "UPDATE buku SET DeletedAt = CURRENT_TIMESTAMP WHERE BukuID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bukuID);

        if (mysqli_stmt_execute($stmt)) {
            // Hapus file cover jika ada
            if (!empty($book['Cover']) && file_exists("../../" . $book['Cover'])) {
                @unlink("../../" . $book['Cover']);
            }
            $_SESSION['success'] = "Buku berhasil dihapus (soft delete)!";
        } else {
            $_SESSION['error'] = "Gagal menghapus buku: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
        header("Location: buku_admin.php");
        exit;
    }
}

// Fungsi untuk mendapatkan semua buku
function getAllBooks($conn)
{
    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.DeletedAt IS NULL
              ORDER BY b.Judul";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan detail buku
function getBookById($conn, $id)
{
    $query = "SELECT * FROM buku WHERE BukuID = ? AND DeletedAt IS NULL";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan semua kategori
function getAllCategories($conn)
{
    $query = "SELECT * FROM kategori ORDER BY NamaKategori";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
