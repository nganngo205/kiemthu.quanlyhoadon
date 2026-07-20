<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h2 class="mb-3">Xin chào</h2>
                <p class="mb-4 text-muted">Chọn một module để bắt đầu quản lý.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <a href="sanpham/list.php" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-box-seam me-2"></i> Quản lý sản phẩm</a>
                    </div>
                    <div class="col-6">
                        <a href="giohang/view.php" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-cart4 me-2"></i> Giỏ hàng</a>
                    </div>
                    <div class="col-6">
                        <a href="thanhtoan/pay.php" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-cash-stack me-2"></i> Thanh toán</a>
                    </div>
                    <div class="col-6">
                        <a href="baocao/report.php" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-bar-chart-line me-2"></i> Báo cáo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
