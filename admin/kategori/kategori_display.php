<div class="page-header">
    <h2 class="page-title">Daftar Kategori Buku</h2>
    <button class="btn btn-primary" id="addCategoryBtn">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Icon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                while ($row = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['NamaKategori']) ?></td>
                        <td><?= htmlspecialchars($row['Deskripsi']) ?></td>
                        <td>
                            <?php if (!empty($row['Icon'])): ?>
                                <img src="../../assets/icon/<?= htmlspecialchars($row['Icon']) ?>" alt="Icon" style="width: 24px; height: 24px;">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon btn-edit edit-category"
                                    data-id="<?= $row['KategoriID'] ?>"
                                    data-nama="<?= htmlspecialchars($row['NamaKategori']) ?>"
                                    data-deskripsi="<?= htmlspecialchars($row['Deskripsi']) ?>"
                                    data-icon="<?= htmlspecialchars($row['Icon']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-delete delete-category"
                                    data-id="<?= $row['KategoriID'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>