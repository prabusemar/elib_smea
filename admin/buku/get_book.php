<?php
session_start();
include '../../config.php';

// Pastikan tidak ada output sebelum header
ob_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    ob_end_flush();
    exit;
}

try {
    $id = (int)($_GET['id'] ?? 0);

    if ($id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
        ob_end_flush();
        exit;
    }

    // Query untuk mendapatkan data buku
    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b 
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.BukuID = ? AND b.DeletedAt IS NULL";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($book = mysqli_fetch_assoc($result)) {
        // Format cover URL
        $coverUrl = '';
        if (!empty($book['Cover'])) {
            $coverUrl = str_starts_with($book['Cover'], 'http')
                ? $book['Cover']
                : rtrim(BASE_URL, '/') . '/' . ltrim($book['Cover'], '/');
        }

        $response = [
            'success' => true,
            'data' => [
                'BukuID' => (int)$book['BukuID'],
                'Judul' => $book['Judul'],
                'Slug' => $book['Slug'],
                'Penulis' => $book['Penulis'],
                'Penerbit' => $book['Penerbit'],
                'TahunTerbit' => $book['TahunTerbit'] ? (int)$book['TahunTerbit'] : null,
                'ISBN' => $book['ISBN'] ?? '',
                'KategoriID' => $book['KategoriID'] ? (int)$book['KategoriID'] : null,
                'NamaKategori' => $book['NamaKategori'] ?? '',
                'Cover' => $coverUrl,
                'FileEbook' => $book['FileEbook'] ?? '',
                'Deskripsi' => $book['Deskripsi'] ?? '',
                'JumlahHalaman' => $book['JumlahHalaman'] ? (int)$book['JumlahHalaman'] : null,
                'Bahasa' => $book['Bahasa'] ?? 'Indonesia',
                'FormatEbook' => $book['FormatEbook'] ?? 'PDF',
                'JenisAkses' => $book['JenisAkses'] ?? 'Free',
                'Visibility' => $book['Visibility'] ?? 'Public',
                'Status' => $book['Status'] ?? 'Published'
            ]
        ];

        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
    ob_end_flush();
}
