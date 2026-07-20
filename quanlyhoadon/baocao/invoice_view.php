<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo '<div class="alert alert-danger">Thiếu mã hoá đơn.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $conn->prepare('SELECT * FROM invoice WHERE id = ?');
$stmt->bind_param('s', $id);
$stmt->execute();
$res = $stmt->get_result();
$invoice = $res->fetch_assoc();
if (!$invoice) {
    echo '<div class="alert alert-warning">Không tìm thấy hoá đơn: ' . htmlspecialchars($id) . '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$itemsStmt = $conn->prepare('SELECT product_id, name, unit_price, quantity, subtotal FROM invoice_item WHERE invoice_id = ?');
$itemsStmt->bind_param('s', $id);
$itemsStmt->execute();
$items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<div class="row">
  <div class="col-md-10 offset-md-1">
    <div class="card">
      <div class="card-body">
        <h4>Hoá đơn: <?php echo htmlspecialchars($invoice['id']); ?></h4>
        <div class="mb-2">Mã phiên / Giỏ: <strong><?php echo htmlspecialchars($invoice['session_id']); ?></strong></div>
        <div class="mb-2">Phương thức: <strong><?php echo htmlspecialchars($invoice['method']); ?></strong></div>
        <div class="mb-2">Trạng thái: <strong><?php echo htmlspecialchars($invoice['status']); ?></strong></div>
        <div class="mb-2">Ngày: <strong><?php echo htmlspecialchars($invoice['created_at']); ?></strong></div>

        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Mã giỏ hàng</th>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?php echo htmlspecialchars($invoice['session_id']); ?></td>
                  <td><?php echo htmlspecialchars($it['product_id']); ?></td>
                  <td><?php echo htmlspecialchars($it['name']); ?></td>
                  <td><?php echo number_format($it['unit_price']); ?></td>
                  <td><?php echo (int)$it['quantity']; ?></td>
                  <td><?php echo number_format($it['subtotal']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" class="text-end"><strong>Tổng</strong></td>
                <td><strong><?php echo number_format($invoice['total']); ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="d-flex justify-content-between">
          <div>
            <a class="btn btn-outline-secondary" href="report.php?from=<?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?>&to=<?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?>">Quay lại báo cáo</a>
          </div>
          <div>
            <a class="btn btn-primary me-2" target="_blank" href="invoice_print.php?id=<?php echo urlencode($invoice['id']); ?>">In / Xuất hoá đơn</a>
            <button class="btn btn-secondary" onclick="window.print(); return false;">In nhanh</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
