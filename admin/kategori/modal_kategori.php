<!-- Modal untuk Tambah/Edit Kategori -->
<div class="modal" id="categoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Kategori Baru</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form id="categoryForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="kategori_id" id="kategori_id">
            <input type="hidden" name="add_category" id="formAction">
            <input type="hidden" name="existing_icon" id="existing_icon">

            <div class="form-group">
                <label for="nama_kategori">Nama Kategori *</label>
                <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="icon">Icon *</label>
                <div id="iconPreviewContainer">
                    <img id="iconPreview" src="" alt="Preview Icon"
                        style="max-width: 100px; max-height: 100px; display: none;"
                        onerror="this.style.display='none'">
                </div>
                <input type="file" id="icon" name="icon" class="form-control" accept="image/*">
                <small class="text-muted">Format: JPG, PNG, SVG. Maksimal 1MB.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel close-modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Form untuk Delete -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="kategori_id" id="delete_kategori_id">
    <input type="hidden" name="delete_category" value="1">
</form>

<script src="<?= BASE_URL ?>/assets/js/kategori_script.js"></script>