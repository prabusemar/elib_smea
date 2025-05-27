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
                // Ensure BASE_URL_JS is defined (it should be from buku_admin.php)
                coverPreview.src = (typeof BASE_URL_JS !== 'undefined' ? BASE_URL_JS : '../..') + '/' + coverPath;
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

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = CSRF_TOKEN; // Use the global variable

            form.appendChild(actionInput);
            form.appendChild(idInput);
            form.appendChild(csrfInput);
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

// TODO: Future improvement - replace alert() with inline error messages next to form fields for better UX.
// Consolidated validation for add book form
const addBookForm = document.getElementById('addBookForm');
if (addBookForm) {
    addBookForm.addEventListener('submit', function (e) {
        let isValid = true;
        let firstErrorMessage = null;

        // Helper to set error and capture first message
        const setError = (element, message) => {
            element.classList.add('is-invalid');
            isValid = false;
            if (!firstErrorMessage) {
                firstErrorMessage = message;
            }
        };

        // Helper to clear error
        const clearError = (element) => {
            element.classList.remove('is-invalid');
        };

        // Required fields check
        const requiredFields = ['judul', 'penulis', 'tahun', 'status', 'driveurl', 'cover'];
        requiredFields.forEach(fieldId => {
            const element = addBookForm.querySelector('#add_' + fieldId); // Use querySelector for robustness
            if (element) {
                let value = element.value.trim();
                if (element.type === 'file') {
                    value = element.files.length > 0 ? element.files[0].name : '';
                }
                if (!value) {
                    setError(element, `Field ${fieldId.replace('_', ' ')} wajib diisi.`);
                } else {
                    clearError(element);
                }
            }
        });

        // Validate year
        const tahunElement = addBookForm.querySelector('#add_tahun');
        if (tahunElement && tahunElement.value.trim() !== '') { // Only validate if not empty (required check handles empty)
            const tahun = parseInt(tahunElement.value, 10);
            const currentYear = new Date().getFullYear();
            if (isNaN(tahun) || tahun < 1900 || tahun > currentYear) {
                setError(tahunElement, `Tahun terbit harus antara 1900 dan ${currentYear}.`);
            } else {
                clearError(tahunElement);
            }
        }

        // Validate Google Drive URL
        const driveUrlElement = addBookForm.querySelector('#add_driveurl');
        if (driveUrlElement && driveUrlElement.value.trim() !== '') {
            if (!driveUrlElement.value.includes('drive.google.com')) {
                setError(driveUrlElement, 'URL Google Drive tidak valid (harus mengandung drive.google.com).');
            } else {
                clearError(driveUrlElement);
            }
        }

        // Validate cover file
        const coverFileElement = addBookForm.querySelector('#add_cover');
        if (coverFileElement && coverFileElement.files.length > 0) {
            const coverFile = coverFileElement.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(coverFile.type)) {
                setError(coverFileElement, 'Format file cover harus JPG, PNG, atau GIF.');
            } else {
                clearError(coverFileElement); // Clear type error if type is valid
            }

            if (coverFile.size > 2 * 1024 * 1024) { // 2MB
                setError(coverFileElement, 'Ukuran file cover terlalu besar (maksimal 2MB).');
            } else {
                // If type was valid and size is valid, clear error. 
                // Need to be careful not to clear an error if type was invalid but size is ok.
                if (validTypes.includes(coverFile.type)) clearError(coverFileElement);
            }
        } else if (coverFileElement && requiredFields.includes('cover')) { // If cover is required and no file
             // This case is handled by the requiredFields check above, but good to be explicit if logic changes
            // setError(coverFileElement, 'Cover buku wajib diupload.');
        }


        if (!isValid) {
            e.preventDefault();
            alert(firstErrorMessage || 'Harap periksa kembali form Anda. Beberapa field tidak valid atau kosong.');
        }
    });
}