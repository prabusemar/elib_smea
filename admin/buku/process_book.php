<?php
session_start();
include '../../config.php';

// Enable error reporting for debugging
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

// Function to generate slug
function generateSlug($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

// Function to validate ISBN
function validateISBN($isbn)
{
    if (empty($isbn)) return true;

    // Basic pattern for ISBN (10 or 13 digits with optional hyphens)
    if (!preg_match('/^(\d{3}-)?\d{1,5}-\d{1,7}-\d{1,7}-\d{1}$|^\d{9}[\dX]$/i', $isbn)) {
        return false;
    }

    return true;
}

// Function to clean cover path
function cleanCoverPath($path)
{
    if (empty($path)) return '';

    // Remove BASE_URL if present
    global $BASE_URL;
    $path = str_replace($BASE_URL, '', $path);

    // Ensure path starts with /uploads/
    if (!str_starts_with($path, '/uploads/')) {
        $path = '/uploads/' . ltrim($path, '/');
    }

    return $path;
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("Unauthorized access", 401);
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        // Validate required fields
        $required = ['judul', 'penulis', 'bahasa', 'jenisAkses', 'visibility', 'status'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} harus diisi!");
            }
        }

        // Prepare data
        $bukuId = isset($_POST['bukuId']) ? (int)$_POST['bukuId'] : 0;
        $judul = trim($_POST['judul']);
        $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : generateSlug($judul);
        $penulis = trim($_POST['penulis']);
        $penerbit = trim($_POST['penerbit'] ?? '');
        $tahun = !empty($_POST['tahun']) ? (int)$_POST['tahun'] : null;
        $isbn = trim($_POST['isbn'] ?? '');
        $kategori = !empty($_POST['kategori']) ? (int)$_POST['kategori'] : null;
        $bahasa = trim($_POST['bahasa']);
        $halaman = !empty($_POST['halaman']) ? (int)$_POST['halaman'] : null;
        $format = trim($_POST['format'] ?? 'PDF');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $jenisAkses = trim($_POST['jenisAkses']);
        $visibility = trim($_POST['visibility']);
        $status = trim($_POST['status']);
        $fileEbook = trim($_POST['fileEbook'] ?? '');

        // Validate ISBN format
        if (!empty($isbn) && !validateISBN($isbn)) {
            throw new Exception("Format ISBN tidak valid. Gunakan format seperti 978-602-06-3724-2 atau 9786020637242");
        }

        // Validate status
        $allowedStatus = ['Published', 'Archived', 'PendingReview', 'Rejected'];
        if (!in_array($status, $allowedStatus)) {
            throw new Exception("Status tidak valid!");
        }

        // Validate jenis akses
        $allowedAccess = ['Free', 'Premium'];
        if (!in_array($jenisAkses, $allowedAccess)) {
            throw new Exception("Jenis akses tidak valid!");
        }

        // Validate visibility
        $allowedVisibility = ['Public', 'Private', 'Draft'];
        if (!in_array($visibility, $allowedVisibility)) {
            throw new Exception("Visibilitas tidak valid!");
        }

        // Validate ebook URL for new book
        if ($action === 'add' && empty($fileEbook)) {
            throw new Exception("Link ebook harus diisi!");
        }

        // Validate Google Drive URL format
        if (!empty($fileEbook) && !preg_match('/^https:\/\/drive\.google\.com\/(file\/d\/[^\/]+\/view|open\?id=[^&]+)/', $fileEbook)) {
            throw new Exception("Format link Google Drive tidak valid! Contoh: https://drive.google.com/file/d/FILE_ID/view");
        }

        // Handle cover upload
        $coverPath = cleanCoverPath($_POST['existingCover'] ?? '');

        if (!empty($_FILES['cover']['name'])) {
            $coverDir = '../../uploads/covers/';
            if (!file_exists($coverDir)) {
                mkdir($coverDir, 0777, true);
            }

            $coverExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $allowedCoverExt = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($coverExt, $allowedCoverExt)) {
                throw new Exception("Format cover tidak didukung. Gunakan JPG, PNG, atau GIF.");
            }

            // Validate file size (max 2MB)
            if ($_FILES['cover']['size'] > 2 * 1024 * 1024) {
                throw new Exception("Ukuran cover maksimal 2MB");
            }

            $coverName = 'cover_' . time() . '.' . $coverExt;
            $coverTmp = $_FILES['cover']['tmp_name'];

            if (!move_uploaded_file($coverTmp, $coverDir . $coverName)) {
                throw new Exception("Gagal mengunggah cover buku");
            }

            // Delete old cover if exists
            if (!empty($coverPath) && file_exists('../../' . ltrim($coverPath, '/'))) {
                @unlink('../../' . ltrim($coverPath, '/'));
            }

            $coverPath = '/uploads/covers/' . $coverName;
        }

        // Check for duplicate slug
        $checkSlugQuery = "SELECT BukuID FROM buku WHERE Slug = ? AND BukuID != ?";
        $stmt = mysqli_prepare($conn, $checkSlugQuery);
        mysqli_stmt_bind_param($stmt, 'si', $slug, $bukuId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            throw new Exception("Slug sudah digunakan oleh buku lain!");
        }
        mysqli_stmt_close($stmt);

        if ($action === 'add') {
            $query = "INSERT INTO buku (
                Judul, Slug, Penulis, Penerbit, TahunTerbit, ISBN, KategoriID, 
                Cover, FileEbook, Deskripsi, JumlahHalaman, Bahasa, FormatEbook, 
                JenisAkses, Visibility, Status, CreatedAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param(
                $stmt,
                'sssssissssisssss',
                $judul,
                $slug,
                $penulis,
                $penerbit,
                $tahun,
                $isbn,
                $kategori,
                $coverPath,
                $fileEbook,
                $deskripsi,
                $halaman,
                $bahasa,
                $format,
                $jenisAkses,
                $visibility,
                $status
            );
        } else {
            $query = "UPDATE buku SET 
                Judul = ?, 
                Slug = ?, 
                Penulis = ?, 
                Penerbit = ?, 
                TahunTerbit = ?, 
                ISBN = ?, 
                KategoriID = ?, 
                Cover = ?, 
                FileEbook = ?, 
                Deskripsi = ?, 
                JumlahHalaman = ?, 
                Bahasa = ?, 
                FormatEbook = ?, 
                JenisAkses = ?, 
                Visibility = ?, 
                Status = ?,
                UpdatedAt = NOW()
                WHERE BukuID = ?";

            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param(
                $stmt,
                'sssssissssisssssi',
                $judul,
                $slug,
                $penulis,
                $penerbit,
                $tahun,
                $isbn,
                $kategori,
                $coverPath,
                $fileEbook,
                $deskripsi,
                $halaman,
                $bahasa,
                $format,
                $jenisAkses,
                $visibility,
                $status,
                $bukuId
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            $bookId = $action === 'add' ? mysqli_insert_id($conn) : $bukuId;
            jsonResponse(true, 'Buku berhasil disimpan', [
                'id' => $bookId,
                'cover_path' => $coverPath,
                'cover_url' => !empty($coverPath) ? BASE_URL . ltrim($coverPath, '/') : null
            ]);
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];

        // Soft delete - update DeletedAt timestamp
        $query = "UPDATE buku SET DeletedAt = NOW() WHERE BukuID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Buku berhasil dihapus (soft delete)');
        } else {
            throw new Exception("Gagal menghapus buku: " . mysqli_error($conn));
        }
    } else {
        throw new Exception("Aksi tidak valid");
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    jsonResponse(false, $e->getMessage());
} finally {
    // Close database connection
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
