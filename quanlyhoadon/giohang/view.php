<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/header.php';

// Lấy cart_id nếu đã có
$cart_id = $_SESSION['cart_id'] ?? null;
$cart_items = [];
$cart_status = null;
if ($cart_id) {
    // Kiểm tra trạng thái giỏ hàng trước
    $check_cart = $conn->prepare('SELECT status FROM cart WHERE id = ? AND status = "pending"');
    $check_cart->bind_param('s', $cart_id);
    $check_cart->execute();
    if ($check_cart->get_result()->num_rows === 0) {
        unset($_SESSION['cart_id']); 
        $cart_id = null;
    } else {
        // Lấy items của giỏ (chỉ lấy những sản phẩm còn tồn tại trong bảng product)
        $stmt = $conn->prepare('SELECT ci.product_id, ci.quantity, ci.price, p.name, p.price AS unit_price 
            FROM cart_item ci 
            INNER JOIN product p ON ci.product_id = p.id 
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC');
        $stmt->bind_param('s', $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_items = $result->fetch_all(MYSQLI_ASSOC);
        
        // Debug log
        if (empty($cart_items)) {
            error_log("Cart $cart_id is empty after query");
        }

        // Lấy trạng thái giỏ
        $s2 = $conn->prepare('SELECT status FROM cart WHERE id = ?');
        $s2->bind_param('s', $cart_id);
        $s2->execute();
        $r2 = $s2->get_result();
        $row = $r2->fetch_assoc();
        $cart_status = $row['status'] ?? null;
    }
}

// Tính tổng tiền
$total = 0;
foreach ($cart_items as $item) {
    if (!empty($item['product_id'])) {
        $total += (float)$item['price'];
    }
}

function currency($v){ return number_format($v, 0, ',', '.'); }
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="mb-0">Giỏ hàng</h4>
                        <?php if (!empty($cart_items) && $cart_id): ?>
                            <small class="text-muted">Mã giỏ: <?php echo htmlspecialchars($cart_id); ?></small>
                            <br>
                            <small class="text-muted">Trạng thái:
                                <?php if ($cart_status === 'completed'): ?>
                                    <span class="badge bg-success">Đã hoàn tất</span>
                                <?php elseif ($cart_status === 'cancelled'): ?>
                                    <span class="badge bg-secondary">Đã hủy</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Chờ thanh toán</span>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="/quanlyhoadon/sanpham/list.php" class="btn btn-outline-secondary btn-sm me-2">Về sản phẩm</a>
                        <a href="/quanlyhoadon/thanhtoan/pay.php" class="btn btn-primary btn-sm">Thanh toán</a>
                    </div>
                </div>

                <?php if (empty($cart_items) || !$cart_items[0]['product_id']): ?>
                    <div class="alert alert-info">Giỏ hàng trống. <a href="/quanlyhoadon/sanpham/list.php">Về sản phẩm</a></div>
                <?php else: ?>
                    <form action="update.php" method="post">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên</th>
                                        <th>Đơn giá</th>
                                        <th style="width:120px">Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($cart_items as $item): ?>
                                    <?php if ($item['product_id']): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo currency($item['unit_price']); ?></td>
                                        <td>
                                            <input type="number" 
                                                name="qty[<?php echo htmlspecialchars($item['product_id']); ?>]" 
                                                class="form-control form-control-sm" 
                                                value="<?php echo (int)$item['quantity']; ?>"
                                                min="1"
                                                oninput="this.value = this.value <= 0 ? 1 : Math.floor(this.value)"
                                                required>
                                        </td>
                                        <td><?php echo currency($item['price']); ?></td>
                                        <td>
                                            <a href="delete.php?id=<?php echo urlencode($item['product_id']); ?>" 
                                               class="btn btn-sm btn-outline-danger">Xóa</a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button type="submit" class="btn btn-secondary btn-sm">Cập nhật số lượng</button>
                            </div>
                            <div>
                                <strong>Tổng tạm: <?php echo currency($total); ?> VNĐ</strong>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <hr>
                <h5>Thêm sản phẩm (ID hoặc Barcode)</h5>
                <form action="add.php" method="post" class="row g-2">
                    <div class="col-md-4">
                        <input name="product_id" class="form-control" placeholder="ID sản phẩm">
                    </div>
                    <div class="col-md-4">
                        <input name="barcode" class="form-control" placeholder="Barcode">
                    </div>
                    <div class="col-md-2">
                        <input name="quantity" type="number" class="form-control" value="1">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="addToCartBtn">Thêm vào giỏ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>

(function(){
    var form = document.querySelector('form[action="add.php"]');
    if (!form) return;
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var fd = new FormData(form);
        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function(r){
            return r.json().catch(function(){ return { success: false, msg: 'Không nhận được phản hồi JSON' }; });
        }).then(function(json){
            if (json && json.success) {
                showToast(json.msg || 'Đã thêm', 'success');
                setTimeout(function(){ location.reload(); }, 700);
            } else {
                showToast((json && json.msg) || 'Lỗi', 'danger');
            }
        }).catch(function(err){
            console.error(err); showToast('Lỗi, vui lòng thử lại', 'danger');
        });
    });
})();
</script>
