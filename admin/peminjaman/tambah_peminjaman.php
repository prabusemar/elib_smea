<?php
session_start();
require_once '../../config.php';

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $term = mysqli_real_escape_string($conn, $_GET['term'] ?? '');

    if ($_GET['ajax'] === 'anggota') {
        $sql = "SELECT MemberID, Nama, Email FROM anggota WHERE Status = 'Active' AND (Nama LIKE ? OR Email LIKE ?) LIMIT 20";
        $stmt = mysqli_prepare($conn, $sql);
        $searchTerm = "%$term%";
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['MemberID'],
                'label' => $row['Nama'] . ' (' . $row['Email'] . ')',
                'value' => $row['Nama'] . ' (' . $row['Email'] . ')'
            ];
        }
        echo json_encode($data);
        exit;
    } elseif ($_GET['ajax'] === 'buku') {
        $sql = "SELECT BukuID, Judul FROM buku WHERE DeletedAt IS NULL AND Judul LIKE ? LIMIT 20";
        $stmt = mysqli_prepare($conn, $sql);
        $searchTerm = "%$term%";
        mysqli_stmt_bind_param($stmt, 's', $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['BukuID'],
                'label' => $row['Judul'],
                'value' => $row['Judul']
            ];
        }
        echo json_encode($data);
        exit;
    }
}

if (!isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$page_title = "Tambah Peminjaman";
include '../../views/header.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="content-wrapper">
    <h1>Tambah Peminjaman</h1>
    <form method="POST" action="peminjaman_handler.php">
        <input type="hidden" name="action" value="tambah">
        <div>
            <label>Anggota</label><br>
            <input type="text" id="anggota_search" autocomplete="off">
            <input type="hidden" name="member_id" id="member_id" required>
            <div id="anggota_suggestions" class="suggestions-dropdown"></div>
            <div id="selected_anggota"></div>
        </div>

        <div>
            <label>Buku</label><br>
            <input type="text" id="buku_search" autocomplete="off">
            <input type="hidden" name="buku_id" id="buku_id" required>
            <div id="buku_suggestions" class="suggestions-dropdown"></div>
            <div id="selected_buku"></div>
        </div>

        <div>
            <label>Tanggal Pinjam</label><br>
            <input type="datetime-local" name="tanggal_pinjam" id="tanggal_pinjam" required>
        </div>

        <div>
            <label>Tanggal Kembali</label><br>
            <input type="datetime-local" name="tanggal_kembali" id="tanggal_kembali" required>
        </div>

        <div class="form-actions">
            <a href="peminjaman_admin.php" class="btn-back">Kembali</a>
            <button type="submit">Simpan</button>
        </div>
    </form>
</div>
<script>
    $(function() {
        function setupAutocomplete(inputSelector, hiddenSelector, resultSelector, ajaxType) {
            let timeout;
            const input = $(inputSelector);
            const hidden = $(hiddenSelector);
            const result = $(resultSelector);

            input.on('input', function() {
                clearTimeout(timeout);
                const term = input.val();
                if (term.length < 2) {
                    $(resultSelector).hide();
                    return;
                }
                timeout = setTimeout(function() {
                    $.get(window.location.pathname, {
                        ajax: ajaxType,
                        term: term
                    }, function(data) {
                        const suggestions = $(resultSelector);
                        suggestions.empty();
                        if (data.length > 0) {
                            data.forEach(item => {
                                $('<div class="suggestion-item">')
                                    .text(item.label)
                                    .on('click', function() {
                                        input.val(item.label);
                                        hidden.val(item.id);
                                        suggestions.hide();
                                    })
                                    .appendTo(suggestions);
                            });
                            suggestions.show();
                        } else {
                            suggestions.hide();
                        }
                    });
                }, 300);
            });

            input.on('blur', function() {
                setTimeout(() => $(resultSelector).hide(), 200);
            });
        }

        setupAutocomplete('#anggota_search', '#member_id', '#anggota_suggestions', 'anggota');
        setupAutocomplete('#buku_search', '#buku_id', '#buku_suggestions', 'buku');
    });

    $(document).ready(function() {
        // Set default pinjam date to now
        const now = new Date();
        // Format as YYYY-MM-DDTHH:MM (datetime-local format)
        const today = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('tanggal_pinjam').value = today;

        // Set minimum kembali date to tomorrow
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowFormatted = new Date(tomorrow.getTime() - tomorrow.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('tanggal_kembali').min = tomorrowFormatted;

        // Update minimum kembali date when pinjam date changes
        $('#tanggal_pinjam').on('change', function() {
            const pinjamDate = new Date(this.value);
            const minKembaliDate = new Date(pinjamDate);
            minKembaliDate.setDate(minKembaliDate.getDate() + 1);

            const formattedMinDate = new Date(minKembaliDate.getTime() - minKembaliDate.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 16);

            $('#tanggal_kembali').attr('min', formattedMinDate);

            // If current kembali value is before new min date, reset it
            if ($('#tanggal_kembali').val() && new Date($('#tanggal_kembali').val()) < minKembaliDate) {
                $('#tanggal_kembali').val(formattedMinDate);
            }
        });
    });
</script>

<style>
    /* Base Styles */
    .content-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 30px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    h1 {
        color: #2c3e50;
        margin-bottom: 30px;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #f1f1f1;
        padding-bottom: 15px;
    }

    form {
        display: grid;
        grid-template-columns: 1fr;
        gap: 25px;
    }

    form>div {
        position: relative;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 15px;
    }

    input[type="text"],
    input[type="datetime-local"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="datetime-local"]:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }

    /* Suggestions Dropdown */
    .suggestions-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 0 0 6px 6px;
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
        z-index: 100;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        display: none;
        margin-top: -1px;
    }

    .suggestion-item {
        padding: 12px 15px;
        cursor: pointer;
        color: #34495e;
        transition: all 0.2s;
        border-bottom: 1px solid #f5f5f5;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover {
        background-color: #f8f9fa;
        color: #3498db;
    }

    /* Selected Items Display */
    #selected_anggota,
    #selected_buku {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        font-size: 14px;
        color: #2c3e50;
        display: none;
    }

    /* Button Styles */
    button[type="submit"] {
        background: #3498db;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
        width: 100%;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    button[type="submit"]:active {
        transform: translateY(0);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 20px;
        }

        h1 {
            font-size: 24px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .suggestions-dropdown {
        animation: fadeIn 0.2s ease-out;
    }

    /* New styles for back button and form actions */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .btn-back {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        flex: 1;
    }

    .btn-back:hover {
        background: #7f8c8d;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-back:active {
        transform: translateY(0);
    }

    button[type="submit"] {
        flex: 1;
        margin-top: 0;
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column;
        }
    }
</style>

<?php include '../../views/footer.php'; ?>