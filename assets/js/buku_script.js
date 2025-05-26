document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const editButtons = document.querySelectorAll('.edit-book');
    const closeButtons = document.querySelectorAll('.close-modal');
    const bookModal = document.getElementById('bookModal');
    const addBookModal = document.getElementById('addBookModal');
    const confirmModal = document.getElementById('confirmModal');

    // Edit button click handler
    editButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('Edit button clicked');

            // Set form values
            document.getElementById('formAction').value = 'update_book';
            document.getElementById('buku_id').value = this.dataset.id;
            document.getElementById('judul').value = this.dataset.judul || '';
            document.getElementById('penulis').value = this.dataset.penulis || '';
            document.getElementById('penerbit').value = this.dataset.penerbit || '';
            document.getElementById('tahun').value = this.dataset.tahun || '';
            document.getElementById('isbn').value = this.dataset.isbn || '';
            document.getElementById('kategori').value = this.dataset.kategori || '';
            document.getElementById('driveurl').value = this.dataset.driveurl || '';
            document.getElementById('deskripsi').value = this.dataset.deskripsi || '';
            document.getElementById('halaman').value = this.dataset.halaman || '';
            document.getElementById('bahasa').value = this.dataset.bahasa || 'Indonesia';
            document.getElementById('format').value = this.dataset.format || 'PDF';
            document.getElementById('status').value = this.dataset.status || 'Free';
            document.getElementById('ukuran').value = this.dataset.ukuran || '';

            // Handle cover preview
            const coverPath = this.dataset.cover || '';
            document.getElementById('existing_cover').value = coverPath;
            const coverPreview = document.getElementById('coverPreview');

            if (coverPath) {
                coverPreview.src = '../../' + coverPath;
                coverPreview.style.display = 'block';
            } else {
                coverPreview.style.display = 'none';
            }

            // Show modal
            bookModal.classList.add('show');
            bookModal.style.display = 'flex';
        });
    });

    // Close button handlers
    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        });
    });

    // Delete button handler
    const deleteButtons = document.querySelectorAll('.delete-book');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const bookId = this.dataset.id;
            const bookTitle = this.dataset.judul;
            document.getElementById('confirmMessage').textContent =
                `Apakah Anda yakin ingin menghapus buku "${bookTitle}"?`;
            document.getElementById('confirmDelete').dataset.id = bookId;
            confirmModal.classList.add('show');
            confirmModal.style.display = 'flex';
        });
    });

    // Confirm delete handler
    document.getElementById('confirmDelete').addEventListener('click', function () {
        const bookId = this.dataset.id;
        if (bookId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'buku_handler.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_book';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'buku_id';
            idInput.value = bookId;

            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Add book button handler
    const addButton = document.getElementById('addBookBtn');
    if (addButton) {
        addButton.addEventListener('click', function () {
            addBookModal.classList.add('show');
            addBookModal.style.display = 'flex';
        });
    }

    // Form validation
    const bookForm = document.getElementById('bookForm');
    if (bookForm) {
        bookForm.addEventListener('submit', function (e) {
            const requiredFields = ['judul', 'penulis', 'tahun', 'status', 'driveurl'];
            let isValid = true;

            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.classList.add('is-invalid');
                    isValid = false;
                } else {
                    element.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi');
                return;
            }

            // Validate year
            const tahun = document.getElementById('tahun').value;
            const currentYear = new Date().getFullYear();
            if (tahun < 1900 || tahun > currentYear) {
                e.preventDefault();
                alert(`Tahun terbit harus antara 1900 dan ${currentYear}`);
                return;
            }

            // Validate Google Drive URL
            const driveurl = document.getElementById('driveurl').value;
            if (!driveurl.includes('drive.google.com')) {
                e.preventDefault();
                alert('URL harus berupa link Google Drive');
                return;
            }

            // Validate cover file if new one is uploaded
            const coverFile = document.getElementById('cover').files[0];
            if (coverFile) {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(coverFile.type)) {
                    e.preventDefault();
                    alert('Format file cover harus JPG, PNG, atau GIF');
                    return;
                }

                if (coverFile.size > 2 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran file cover terlalu besar (maks 2MB)');
                    return;
                }
            }
        });
    }

    // Click outside modal to close
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('show');
            e.target.style.display = 'none';
        }
    });
});

// Handle modal tambah buku
document.getElementById('addBookBtn').addEventListener('click', function () {
    // Reset form
    document.getElementById('addBookForm').reset();
    document.getElementById('add_cover_preview').style.display = 'none';

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

// Function pagination
$(document).ready(function () {
    // Reset ke halaman 1 saat melakukan search atau filter
    $('form.advanced-filter-form').on('submit', function () {
        $(this).append('<input type="hidden" name="page" value="1">');
    });

    // Pastikan semua link pagination memiliki parameter yang benar
    $('.pagination a').each(function () {
        const url = new URL(this.href);
        // Replace the following values with actual JavaScript variables or values as needed
        url.searchParams.set('judul', filters.judul || '');
        url.searchParams.set('penulis', filters.penulis || '');
        url.searchParams.set('kategori', filters.kategori || '');
        url.searchParams.set('tahun', filters.tahun || '');
        url.searchParams.set('status', filters.status || '');
        this.href = url.toString();
    });
})