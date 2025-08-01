<?php
session_start();
require_once '../../config.php';

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $term = mysqli_real_escape_string($conn, $_GET['term'] ?? '');

    if ($_GET['ajax'] === 'anggota') {
        $sql = "SELECT MemberID, Nama, Email, Status FROM anggota WHERE (Status = 'Active' OR Status = 'Suspended' OR Status = 'Banned') AND (Nama LIKE ? OR Email LIKE ?) LIMIT 20";
        $stmt = mysqli_prepare($conn, $sql);
        $searchTerm = "%$term%";
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $disabled = ($row['Status'] !== 'Active') ? ' (Status: ' . $row['Status'] . ')' : '';
            $data[] = [
                'id' => $row['MemberID'],
                'label' => $row['Nama'] . ' (' . $row['Email'] . ')' . $disabled,
                'value' => $row['Nama'] . ' (' . $row['Email'] . ')',
                'status' => $row['Status']
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
    } elseif ($_GET['ajax'] === 'check_status') {
        $memberId = (int)$_GET['member_id'];

        // Debug: Tampilkan parameter yang diterima
        error_log("MEMBER ID: " . $memberId);

        // Query data anggota
        $sql = "SELECT Status, JenisAkun FROM anggota WHERE MemberID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $memberId);

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error anggota: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        $anggota = mysqli_fetch_assoc($result);

        // Query peminjaman dengan cara yang lebih reliable
        $sql2 = "SELECT COUNT(*) as jumlah FROM peminjaman 
             WHERE MemberID = ? AND Status = 'Active'";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, 'i', $memberId);

        if (!mysqli_stmt_execute($stmt2)) {
            error_log("Error peminjaman: " . mysqli_error($conn));
        }

        $result2 = mysqli_stmt_get_result($stmt2);
        $row = mysqli_fetch_assoc($result2);
        $jumlah = $row['jumlah'] ?? 0;

        // Debug: Tampilkan hasil query
        error_log("JUMLAH PEMINJAMAN: " . $jumlah);

        $response = [
            'status' => $anggota['Status'] ?? 'Unknown',
            'jenis_akun' => $anggota['JenisAkun'] ?? 'Free',
            'jumlah_pinjam' => (int)$jumlah
        ];

        error_log("Sending response: " . json_encode($response));
        echo json_encode($response);
        exit;
    } elseif ($_GET['ajax'] === 'check_existing') {
        // Tambahan logika validasi AJAX
        $memberId = (int)($_GET['member_id'] ?? 0);
        $bukuId = (int)($_GET['buku_id'] ?? 0);

        $sql = "SELECT COUNT(*) as total FROM peminjaman WHERE MemberID = ? AND BukuID = ? AND Status = 'Active'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $memberId, $bukuId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        echo json_encode(['exists' => $row['total'] > 0]);
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
    <form method="POST" action="peminjaman_handler.php" id="form-peminjaman">
        <input type="hidden" name="action" value="tambah">
        <div>
            <label>Anggota</label><br>
            <input type="text" id="anggota_search" autocomplete="off">
            <input type="hidden" name="member_id" id="member_id" required>
            <div id="anggota_suggestions" class="suggestions-dropdown"></div>
            <div id="selected_anggota"></div>
            <div id="status-warning" class="alert" style="display:none;"></div>
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
            <button type="submit" id="submit-btn">Simpan</button>
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
                                const suggestionItem = $('<div class="suggestion-item">')
                                    .text(item.label)
                                    .on('click', function() {
                                        input.val(item.value);
                                        hidden.val(item.id);
                                        suggestions.hide();

                                        if (ajaxType === 'anggota') {
                                            // Panggil checkMemberStatus dengan member ID yang baru dipilih
                                            setTimeout(function() {
                                                checkMemberStatus();
                                            }, 100);
                                        }
                                    });

                                if (ajaxType === 'anggota' && item.status !== 'Active') {
                                    suggestionItem.css('color', '#dc3545');
                                }

                                suggestionItem.appendTo(suggestions);
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

        function checkMemberStatus(status = null) {
            if (status) {
                handleStatusResponse({
                    status: status
                });
                return;
            }

            const memberId = $('#member_id').val();
            if (!memberId) return;

            console.log("Checking status for MemberID:", memberId); // Debug log

            $.get(window.location.pathname, {
                ajax: 'check_status',
                member_id: memberId
            }, function(response) {
                console.log("AJAX Response:", response); // Debug log
                handleStatusResponse(response);
            }, 'json').fail(function(xhr, status, error) {
                console.error("AJAX Error:", error);
                console.error("Response:", xhr.responseText);
            });
        }

        function handleStatusResponse(response) {
            console.log("RESPONSE:", response); // Debug response di console browser

            const warning = $('#status-warning');
            const submitBtn = $('#submit-btn');

            // Pastikan response adalah object
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error("Error parsing response:", e);
                    return;
                }
            }

            console.log("Parsed response:", response);

            if (response.status !== 'Active') {
                warning.removeClass('alert-success')
                    .addClass('alert-warning')
                    .text('Anggota ini memiliki status: ' + response.status + '. Tidak dapat melakukan peminjaman.')
                    .show();
                submitBtn.prop('disabled', true);
                return;
            }

            const jenis = response.jenis_akun || 'Free';
            const jumlah = parseInt(response.jumlah_pinjam) || 0;

            console.log("JENIS AKUN:", jenis, "JUMLAH:", jumlah); // Debug nilai

            let batas = jenis === 'Premium' ? 15 : 5;

            if (jumlah >= batas) {
                warning.removeClass('alert-success')
                    .addClass('alert-warning')
                    .text(`Anggota dengan akun ${jenis} telah meminjam ${jumlah} buku. Maksimal ${batas} buku.`)
                    .show();
                submitBtn.prop('disabled', true);
            } else {
                warning.removeClass('alert-warning')
                    .addClass('alert-success')
                    .text(`Anggota aktif (${jenis}). Saat ini meminjam ${jumlah} buku. Dapat melakukan peminjaman.`)
                    .show();
                submitBtn.prop('disabled', false);
            }
        }


        // Set default pinjam date to now
        const now = new Date();
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

            if ($('#tanggal_kembali').val() && new Date($('#tanggal_kembali').val()) < minKembaliDate) {
                $('#tanggal_kembali').val(formattedMinDate);
            }
        });

        // Tambahan: Cek peminjaman aktif sebelum submit
        $('#form-peminjaman').on('submit', function(e) {
            const memberId = $('#member_id').val();
            const bukuId = $('#buku_id').val();

            if (!memberId || !bukuId) {
                alert("Harap pilih anggota dan buku terlebih dahulu.");
                e.preventDefault();
                return;
            }

            e.preventDefault(); // Tunda pengiriman

            $.get(window.location.pathname, {
                ajax: 'check_existing',
                member_id: memberId,
                buku_id: bukuId
            }, function(response) {
                if (response.exists) {
                    alert("Anggota ini sudah meminjam buku yang sama dan belum dikembalikan.");
                } else {
                    $('#form-peminjaman')[0].submit(); // Submit form jika aman
                }
            }, 'json');
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
    }

    /* Status Warning Styles */
    .alert {
        padding: 12px 15px;
        border-radius: 6px;
        margin-top: 10px;
        font-size: 14px;
        display: none;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
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
    }

    button[type="submit"]:hover:not(:disabled) {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    button[type="submit"]:disabled {
        background-color: #95a5a6 !important;
        transform: none !important;
        box-shadow: none !important;
        cursor: not-allowed;
        opacity: 0.6;
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

    /* Form Actions */
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

    button[type="submit"] {
        flex: 1;
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column;
        }
    }
</style>

<?php include '../../views/footer.php'; ?>