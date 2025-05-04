document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const modal = document.getElementById('categoryModal');
    const addBtn = document.getElementById('addCategoryBtn');
    const closeBtns = document.querySelectorAll('.close-modal, .btn-cancel');

    // Form elements
    const categoryForm = document.getElementById('categoryForm');
    const formAction = document.getElementById('formAction');
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('iconPreview');
    const existingIconInput = document.getElementById('existing_icon');

    // Delete form
    const deleteForm = document.getElementById('deleteForm');

    // Show modal for add
    addBtn.addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Tambah Kategori Baru';
        formAction.name = 'add_category';
        categoryForm.reset();
        document.getElementById('kategori_id').value = '';
        existingIconInput.value = '';
        iconPreview.style.display = 'none';
        modal.style.display = 'flex';
    });

    // Show modal for edit
    document.querySelectorAll('.edit-category').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            formAction.name = 'update_category';
            document.getElementById('kategori_id').value = this.dataset.id;
            document.getElementById('nama_kategori').value = this.dataset.nama;
            document.getElementById('deskripsi').value = this.dataset.deskripsi;

            // Handle icon preview
            if (this.dataset.icon) {
                existingIconInput.value = this.dataset.icon;
                iconPreview.src = 'assets/icon/' + this.dataset.icon;
                iconPreview.style.display = 'block';
            } else {
                existingIconInput.value = '';
                iconPreview.style.display = 'none';
            }

            modal.style.display = 'flex';
        });
    });

    // Close modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });

    // Preview image when file selected
    iconInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                iconPreview.src = e.target.result;
                iconPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Delete confirmation
    document.querySelectorAll('.delete-category').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
                document.getElementById('delete_kategori_id').value = this.dataset.id;
                deleteForm.submit();
            }
        });
    });

    // Form validation
    categoryForm.addEventListener('submit', function (e) {
        if (!document.getElementById('nama_kategori').value.trim() ||
            (!document.getElementById('icon').files[0] && !existingIconInput.value)) {
            e.preventDefault();
            alert('Nama Kategori dan Icon wajib diisi!');
        }
    });
});