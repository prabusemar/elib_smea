<?php
session_start();
header('Content-Type: application/json');
include '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
        exit;
    }

    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b 
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.BukuID = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($book = mysqli_fetch_assoc($result)) {
        // Pastikan field Cover dan FileEbook mengandung path lengkap jika ada
        if (!empty($book['Cover']) && !str_starts_with($book['Cover'], 'http')) {
            $book['Cover'] = BASE_URL . $book['Cover'];
        }

        if (!empty($book['FileEbook']) && !str_starts_with($book['FileEbook'], 'http')) {
            $book['FileEbook'] = BASE_URL . $book['FileEbook'];
        }

        echo json_encode([
            'success' => true,
            'data' => $book
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
