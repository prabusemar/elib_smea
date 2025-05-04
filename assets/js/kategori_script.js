document.addEventListener('DOMContentLoaded', function () {
    console.log('Script loaded'); // Debugging

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
        console.log('Add button clicked'); // Debugging
        document.getElementById('modalTitle').textContent = 'Tambah Kategori Baru';
        formAction.name = 'add_category';
        categoryForm.reset();
        document.getElementById('kategori_id').value = '';
        existingIconInput.value = '';
        iconPreview.style.display = 'none';
        iconPreview.src = '';
        modal.style.display = 'flex';
    });

    // Fixed: Event delegation for edit buttons
    document.addEventListener('click', function (e) {
        // Check if edit button was clicked
        const editBtn = e.target.closest('.edit-category');
        if (editBtn) {
            e.preventDefault();
            console.log('Edit button clicked', editBtn.dataset); // Debugging

            // Set modal title and action
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            formAction.name = 'update_category';

            // Fill form with existing data
            document.getElementById('kategori_id').value = editBtn.dataset.id;
            document.getElementById('nama_kategori').value = editBtn.dataset.nama;
            document.getElementById('deskripsi').value = editBtn.dataset.deskripsi;

            // Handle icon preview
            if (editBtn.dataset.icon) {
                existingIconInput.value = editBtn.dataset.icon;
                const iconPath = window.location.origin + '/assets/icon/' + editBtn.dataset.icon;
                console.log('Loading icon from:', iconPath); // Debugging

                iconPreview.src = iconPath;
                iconPreview.style.display = 'block';

                iconPreview.onerror = function () {
                    console.error('Failed to load icon:', iconPath);
                    this.style.display = 'none';
                };

                iconPreview.onload = function () {
                    console.log('Icon loaded successfully');
                };
            } else {
                existingIconInput.value = '';
                iconPreview.style.display = 'none';
                iconPreview.src = '';
            }

            // Show modal
            modal.style.display = 'flex';
        }
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
                existingIconInput.value = '';
            };
            reader.readAsDataURL(file);
        }
    });

    // Delete confirmation
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-category')) {
            e.preventDefault();
            const button = e.target.closest('.delete-category');
            if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
                document.getElementById('delete_kategori_id').value = button.dataset.id;
                deleteForm.submit();
            }
        }
    });

    // Form validation
    categoryForm.addEventListener('submit', function (e) {
        if (!document.getElementById('nama_kategori').value.trim()) {
            e.preventDefault();
            alert('Nama Kategori wajib diisi!');
            return;
        }

        if (!document.getElementById('icon').files[0] && !existingIconInput.value) {
            e.preventDefault();
            alert('Icon wajib diisi!');
            return;
        }
    });

    console.log('Event listeners setup completed'); // Debugging
});