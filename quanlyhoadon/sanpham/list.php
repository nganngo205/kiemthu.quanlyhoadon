<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/header.php';

$result = $conn->query('SELECT * FROM product ORDER BY id');
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="card-title mb-0">Danh sách sản phẩm</h4>
                                <div class="small small-muted">Tổng: <span id="rowCount"></span> sản phẩm</div>
                            </div>
                            <div class="d-flex align-items-center">
                                <input type="search" id="tableSearch" class="form-control form-control-sm me-2 search-input" placeholder="Tìm theo ID / tên / barcode">
                                <button class="btn btn-light me-2" onclick="location.href='/quanlyhoadon/index.php'">Trang chính</button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" data-mode="add">
                                    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Barcode</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá (VNĐ)</th>
                                <th>Tồn</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['barcode']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price']); ?></td>
                                <td>
                                    <?php 
                                    $quantity = (int)$row['quantity'];
                                    echo $quantity;
                                    if ($quantity < 5) {
                                        echo ' <span class="text-danger">(sắp hết hàng)</span>';
                                    }
                                    ?>
                                </td>
                                <td style="width:180px;">
                                    <button class="btn btn-sm btn-outline-secondary me-1" 
                                        data-bs-toggle="modal" data-bs-target="#productModal" data-mode="edit"
                                        data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                        data-barcode="<?php echo htmlspecialchars($row['barcode']); ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                        data-quantity="<?php echo (int)$row['quantity']; ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal (Add/Edit) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/quanlyhoadon/sanpham/add.php">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm/Sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ID sản phẩm</label>
                        <input name="id" class="form-control" required pattern="[A-Z]{2}\d{6}" title="ID sản phẩm: 2 chữ cái in hoa + 6 số (ví dụ: AB123456)">
                        <small class="form-text text-muted">ID sản phẩm: 2 chữ cái in hoa + 6 số (ví dụ: AB123456)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input name="barcode" class="form-control" readonly>
                        <small class="form-text text-muted">Barcode sẽ được tự động tạo</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input name="name" class="form-control" required>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-6">
                            <label class="form-label">Giá (VNĐ)</label>
                            <input name="price" type="number" step="1000" min="1000" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Số lượng</label>
                            <input name="quantity" type="number" min="1" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <p>Bạn có chắc muốn xóa sản phẩm <strong id="delId"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <a id="confirmDeleteBtn" class="btn btn-danger btn-sm" href="#">Xóa</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// populate delete modal
var deleteModal = document.getElementById('deleteModal');
if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        document.getElementById('delId').textContent = id;
        document.getElementById('confirmDeleteBtn').href = '/quanlyhoadon/sanpham/delete.php?id=' + encodeURIComponent(id);
    });
}

// client-side search
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('tableSearch');
    var tbody = document.querySelector('table.table tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var countEl = document.getElementById('rowCount');
    function updateCount(){
        var visible = rows.filter(r => r.style.display !== 'none').length;
        if (countEl) countEl.textContent = visible;
    }
    updateCount();
    if (!input) return;
    input.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        rows.forEach(function(r){
            // search sanpham = ID (1), Barcode (2), Name (3)
            var idCell = (r.querySelector('td:nth-child(1)')||{textContent:''}).textContent.toLowerCase();
            var barcodeCell = (r.querySelector('td:nth-child(2)')||{textContent:''}).textContent.toLowerCase();
            var nameCell = (r.querySelector('td:nth-child(3)')||{textContent:''}).textContent.toLowerCase();
            var hay = idCell + ' ' + barcodeCell + ' ' + nameCell;
            r.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
        });
        updateCount();
    });
});

// AJAX submit for add/edit form inside modal
(function(){
    var modal = document.getElementById('productModal');
    if (!modal) return;
    var form = modal.querySelector('form');
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var action = form.getAttribute('action') || '/quanlyhoadon/sanpham/add.php';
        var fd = new FormData(form);
        fetch(action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(r => r.json()).then(json => {
            if (json && json.success) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
                showToast(json.msg || 'Thành công', 'success');
                setTimeout(function(){ location.reload(); }, 800);
            } else {
                showToast((json && json.msg) || 'Lỗi', 'danger');
            }
        }).catch(err => {
            console.error(err); showToast('Lỗi, vui lòng nhập lại', 'danger');
        });
    });
})();

// AJAX delete
(function(){
    var delBtn = document.getElementById('confirmDeleteBtn');
    if (!delBtn) return;
    delBtn.addEventListener('click', function(e){
        e.preventDefault();
        var href = delBtn.getAttribute('href');
        if (!href) return;
        fetch(href, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json()).then(json => {
                if (json && json.success) {
                    var bs = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    if (bs) bs.hide();
                    showToast(json.msg || 'Đã xóa', 'success');
                    setTimeout(function(){ location.reload(); }, 700);
                } else {
                    showToast((json && json.msg) || 'Không thể xóa', 'danger');
                }
            }).catch(err => { console.error(err); showToast('Lỗi, vui lòng nhập lại', 'danger'); });
    });
})();
</script>
