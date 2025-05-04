<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Category
    if (isset($_POST['add_category'])) {
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

        // Handle file upload
        $icon = '';
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $target_dir = __DIR__ . "/../../assets/icon/";
            $file_extension = pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["icon"]["tmp_name"], $target_file)) {
                $icon = $new_filename;
            }
        }

        if (empty($nama) || empty($icon)) {
            $_SESSION['error'] = "Nama Kategori dan Icon wajib diisi!";
        } else {
            $query = "INSERT INTO kategori (NamaKategori, Deskripsi, Icon) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sss", $nama, $deskripsi, $icon);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Kategori berhasil ditambahkan!";
            mysqli_stmt_close($stmt);
        }
    }
    // Update Category
    elseif (isset($_POST['update_category'])) {
        $id = (int)$_POST['kategori_id'];
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

        // Handle file upload
        $icon = $_POST['existing_icon'];
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $target_dir = __DIR__ . "/../../assets/icon/";
            $file_extension = pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["icon"]["tmp_name"], $target_file)) {
                // Delete old file if exists
                if (!empty($_POST['existing_icon'])) {
                    $old_file = $target_dir . $_POST['existing_icon'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $icon = $new_filename;
            }
        }

        $query = "UPDATE kategori SET NamaKategori=?, Deskripsi=?, Icon=? WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $deskripsi, $icon, $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Kategori berhasil diperbarui!";
        mysqli_stmt_close($stmt);
    }
    // Delete Category
    elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['kategori_id'];

        // Get icon filename to delete
        $query = "SELECT Icon FROM kategori WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row && !empty($row['Icon'])) {
            $icon_file = __DIR__ . "/../../assets/icon/" . $row['Icon'];
            if (file_exists($icon_file)) {
                unlink($icon_file);
            }
        }

        $query = "DELETE FROM kategori WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Kategori berhasil dihapus!";
        mysqli_stmt_close($stmt);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all categories
$query = "SELECT * FROM kategori ORDER BY NamaKategori";
$categories = mysqli_query($conn, $query);
