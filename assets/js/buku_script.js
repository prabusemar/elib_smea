document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const bookModal = document.getElementById('bookModal');
    const confirmModal = document.getElementById('confirmModal');
    const addBtn = document.getElementById('addBookBtn');
    const closeBtns = document.querySelectorAll('.close-modal');

    // Form elements
    const bookForm = document.getElementById('bookForm');
    const formAction = document.getElementById('formAction');
    const bukuIdInput = document.getElementById('buku_id');
    const existingCoverInput = document.getElementById('existing_cover');

    // Confirm modal elements
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    let bookToDelete = null;

    // Show modal for add
    addBtn.addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Tambah Buku Baru';
        formAction.value = 'add_book';
        bookForm.reset();
        bukuIdInput.value = '';
        existingCoverInput.value = '';
        document.getElementById('cover').value = '';
        document.getElementById('coverPreview').style.display = 'none';
        document.getElementById('coverPreview').src = '';
        bookModal.style.display = 'flex';
    });

    // Show modal for edit
    document.addEventListener('click', function (e) {
        if (e.target.closest('.edit-book')) {
            const btn = e.target.closest('.edit-book');

            document.getElementById('modalTitle').textContent = 'Edit Buku';
            formAction.value = 'update_book';
            bukuIdInput.value = btn.dataset.id;

            // Fill form with existing data
            document.getElementById('judul').value = btn.dataset.judul;
            document.getElementById('penulis').value = btn.dataset.penulis;
            document.getElementById('penerbit').value = btn.dataset.penerbit;
            document.getElementById('tahun').value = btn.dataset.tahun;
            document.getElementById('isbn').value = btn.dataset.isbn;
            document.getElementById('kategori').value = btn.dataset.kategori;
            document.getElementById('driveurl').value = btn.dataset.driveurl;
            document.getElementById('deskripsi').value = btn.dataset.deskripsi;
            document.getElementById('halaman').value = btn.dataset.halaman;
            document.getElementById('bahasa').value = btn.dataset.bahasa || 'Indonesia';
            document.getElementById('format').value = btn.dataset.format;
            document.getElementById('ukuran').value = btn.dataset.ukuran;
            document.getElementById('status').value = btn.dataset.status || 'Free';
            document.getElementById('cover').value = btn.dataset.cover;

            // Set rating
            const rating = parseInt(btn.dataset.rating) || 0;
            document.getElementById('rating').value = rating;

            // Handle cover preview
            existingCoverInput.value = btn.dataset.cover;
            if (btn.dataset.cover) {
                document.getElementById('coverPreview').src = btn.dataset.cover;
                document.getElementById('coverPreview').style.display = 'block';
                document.getElementById('coverPreview').onerror = function () {
                    this.style.display = 'none';
                };
            } else {
                document.getElementById('coverPreview').style.display = 'none';
            }

            bookModal.style.display = 'flex';
        }

        // Handle delete button click
        if (e.target.closest('.delete-book')) {
            const btn = e.target.closest('.delete-book');
            bookToDelete = {
                id: btn.dataset.id,
                judul: btn.dataset.judul
            };

            document.getElementById('confirmMessage').textContent =
                `Apakah Anda yakin ingin menghapus buku "${btn.dataset.judul}"?`;
            confirmModal.style.display = 'flex';
        }
    });

    // Close modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal('bookModal');
            closeModal('confirmModal');
            closeModal('addBookModal');
        });
    });

    // Preview cover when URL changes
    document.getElementById('cover').addEventListener('input', function () {
        if (this.value) {
            document.getElementById('coverPreview').src = this.value;
            document.getElementById('coverPreview').style.display = 'block';
            document.getElementById('coverPreview').onerror = function () {
                this.style.display = 'none';
            };
        } else {
            document.getElementById('coverPreview').style.display = 'none';
        }
    });

    // Confirm delete
    confirmDeleteBtn.addEventListener('click', function () {
        if (bookToDelete) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'buku_handler.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_book';
            form.appendChild(actionInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'buku_id';
            idInput.value = bookToDelete.id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // Form validation
    bookForm.addEventListener('submit', function (e) {
        const judul = document.getElementById('judul').value.trim();
        const penulis = document.getElementById('penulis').value.trim();
        const tahun = document.getElementById('tahun').value;
        const status = document.getElementById('status').value;
        const driveurl = document.getElementById('driveurl').value.trim();
        const bahasa = document.getElementById('bahasa').value;

        if (!judul || !penulis || !tahun || !status || !driveurl || !bahasa) {
            e.preventDefault();
            alert('Field yang wajib diisi tidak boleh kosong!');
            return;
        }

        // Validate year
        const currentYear = new Date().getFullYear();
        if (tahun < 1900 || tahun > currentYear) {
            e.preventDefault();
            alert(`Tahun terbit harus antara 1900 dan ${currentYear}`);
            return;
        }

        // Validate Google Drive URL
        if (!driveurl.includes('drive.google.com')) {
            e.preventDefault();
            alert('URL harus berupa link Google Drive!');
            return;
        }

        // Validate cover URL if provided
        const coverUrl = document.getElementById('cover').value.trim();
        if (coverUrl) {
            try {
                new URL(coverUrl);
            } catch (_) {
                e.preventDefault();
                alert('URL cover tidak valid!');
                return;
            }
        }
    });
});

// Handle modal tambah buku
document.getElementById('addBookBtn').addEventListener('click', function () {
    // Reset form
    document.getElementById('addBookForm').reset();
    document.getElementById('add_cover_preview').style.display = 'none';

    // Prefill data jika ada di session (setelah validasi gagal)
    // NOTE: To prefill form data from the session, inject a <script> block with the formData variable in your HTML/PHP file, like:
    // <script>const formData = <?= json_encode($_SESSION['form_data']) ?>;</script>
    if (typeof formData !== 'undefined') {
        for (const key in formData) {
            const element = document.getElementById('add_' + key);
            if (element) {
                element.value = formData[key];

                // Handle cover preview
                if (key === 'cover' && formData[key]) {
                    document.getElementById('add_cover_preview').src = formData[key];
                    document.getElementById('add_cover_preview').style.display = 'block';
                }
            }
        }
    }

    // Tampilkan modal
    document.getElementById('addBookModal').style.display = 'flex';
});

// Preview cover saat URL diubah
document.getElementById('add_cover').addEventListener('input', function () {
    const preview = document.getElementById('add_cover_preview');
    if (this.value) {
        preview.src = this.value;
        preview.style.display = 'block';
        preview.onerror = function () {
            this.style.display = 'none';
        };
    } else {
        preview.style.display = 'none';
    }
});

// Validasi form tambah buku
document.getElementById('addBookForm').addEventListener('submit', function (e) {
    const requiredFields = [
        'judul', 'penulis', 'tahun', 'status', 'driveurl'
    ];

    let isValid = true;

    requiredFields.forEach(field => {
        const element = document.getElementById('add_' + field);
        if (!element.value.trim()) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
        }
    });

    // Validasi tahun
    const tahun = document.getElementById('add_tahun').value;
    const currentYear = new Date().getFullYear();
    if (tahun < 1900 || tahun > currentYear) {
        document.getElementById('add_tahun').classList.add('is-invalid');
        isValid = false;
    }

    // Validasi Google Drive URL
    const driveUrl = document.getElementById('add_driveurl').value;
    if (!driveUrl.includes('drive.google.com')) {
        document.getElementById('add_driveurl').classList.add('is-invalid');
        isValid = false;
    }

    // Validasi cover URL jika diisi
    const coverUrl = document.getElementById('add_cover').value;
    if (coverUrl) {
        try {
            new URL(coverUrl);
        } catch (_) {
            document.getElementById('add_cover').classList.add('is-invalid');
            isValid = false;
        }
    }

    if (!isValid) {
        e.preventDefault();
        alert('Harap periksa kembali form Anda. Beberapa field tidak valid.');
    }
});

// Preview cover saat file dipilih
document.getElementById('add_cover').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('add_cover_preview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Fungsi untuk mendapatkan info file dari Google Drive
document.getElementById('btnGetFileInfo').addEventListener('click', function () {
    const driveUrl = document.getElementById('add_driveurl').value.trim();

    if (!driveUrl) {
        alert('Harap masukkan URL Google Drive terlebih dahulu');
        return;
    }

    // Validasi format URL Google Drive
    if (!driveUrl.includes('drive.google.com')) {
        alert('URL harus berupa link Google Drive yang valid');
        return;
    }

    // Tampilkan loading
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;

    // Simulasikan pengambilan info file (di production bisa diganti dengan API call)
    setTimeout(function () {
        // Contoh ekstraksi info dari URL (di production perlu API yang lebih robust)
        let fileFormat = 'PDF';
        let fileSize = (Math.random() * 5 + 1).toFixed(2); // Random 1-6 MB

        // Coba deteksi format dari URL
        if (driveUrl.includes('.pdf')) fileFormat = 'PDF';
        else if (driveUrl.includes('.epub')) fileFormat = 'EPUB';
        else if (driveUrl.includes('.doc') || driveUrl.includes('.docx')) fileFormat = 'DOCX';

        // Update form
        document.getElementById('add_format').value = fileFormat;
        document.getElementById('add_ukuran').value = fileSize;

        // Tampilkan info
        document.getElementById('fileInfoText').textContent =
            `Format: ${fileFormat}, Ukuran: ${fileSize} MB`;
        document.getElementById('fileInfoContainer').style.display = 'block';

        // Reset button
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1500);
});

// Validasi form tambah buku
document.getElementById('addBookForm').addEventListener('submit', function (e) {
    const requiredFields = [
        'judul', 'penulis', 'tahun', 'status', 'driveurl', 'cover'
    ];

    let isValid = true;

    requiredFields.forEach(field => {
        const element = document.getElementById('add_' + field);
        if (!element.value.trim()) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
        }
    });

    // Validasi tahun
    const tahun = document.getElementById('add_tahun').value;
    const currentYear = new Date().getFullYear();
    if (tahun < 1900 || tahun > currentYear) {
        document.getElementById('add_tahun').classList.add('is-invalid');
        isValid = false;
    }

    // Validasi Google Drive URL
    const driveUrl = document.getElementById('add_driveurl').value;
    if (!driveUrl.includes('drive.google.com')) {
        document.getElementById('add_driveurl').classList.add('is-invalid');
        isValid = false;
    }

    // Validasi file cover
    const coverFile = document.getElementById('add_cover').files[0];
    if (!coverFile) {
        document.getElementById('add_cover').classList.add('is-invalid');
        isValid = false;
    } else {
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(coverFile.type)) {
            alert('Format file cover harus JPG, PNG, atau GIF');
            isValid = false;
        }

        // Validasi ukuran file (max 2MB)
        if (coverFile.size > 2 * 1024 * 1024) {
            alert('Ukuran file cover terlalu besar (maksimal 2MB)');
            isValid = false;
        }
    }

    if (!isValid) {
        e.preventDefault();
        alert('Harap periksa kembali form Anda. Beberapa field tidak valid.');
    }
});
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}