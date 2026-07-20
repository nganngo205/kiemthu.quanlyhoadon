<?php
// Print-friendly invoice page. Opens print dialog on load; suitable for printing or saving as PDF from browser.
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
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hoá đơn <?php echo htmlspecialchars($invoice['id']); ?></title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #222; margin: 20px; }
    .invoice-box { max-width: 800px; margin: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 6px; border: 1px solid #ddd; }
    th { background: #f8f8f8; }
    .text-right { text-align: right; }
    .no-border { border: none; }
    @media print {
      .no-print { display: none; }
      body { margin: 0; }
    }
  </style>
</head>
<body>
  <div class="invoice-box">
    <h2>Hoá đơn bán hàng</h2>
    <div>Mã hoá đơn: <strong><?php echo htmlspecialchars($invoice['id']); ?></strong></div>
    <div>Mã phiên / Giỏ hàng: <strong><?php echo htmlspecialchars($invoice['session_id']); ?></strong></div>
    <div>Phương thức: <strong><?php echo htmlspecialchars($invoice['method']); ?></strong></div>
    <div>Trạng thái: <strong><?php echo htmlspecialchars($invoice['status']); ?></strong></div>
    <div>Ngày: <strong><?php echo htmlspecialchars($invoice['created_at']); ?></strong></div>

    <hr>

    <table>
      <thead>
        <tr>
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
            <td><?php echo htmlspecialchars($it['product_id']); ?></td>
            <td><?php echo htmlspecialchars($it['name']); ?></td>
            <td class="text-right"><?php echo number_format($it['unit_price']); ?></td>
            <td class="text-right"><?php echo (int)$it['quantity']; ?></td>
            <td class="text-right"><?php echo number_format($it['subtotal']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-right"><strong>Tổng</strong></td>
          <td class="text-right"><strong><?php echo number_format($invoice['total']); ?></strong></td>
        </tr>
      </tfoot>
    </table>

    <div style="margin-top:20px;">
      <em>Xin cảm ơn quý khách. Hóa đơn này có thể được in hoặc lưu sang PDF bằng chức năng In của trình duyệt.</em>
    </div>

    <div class="no-print" style="margin-top:20px;">
      <button onclick="window.print();">In / Lưu PDF</button>
      <a href="invoice_view.php?id=<?php echo urlencode($invoice['id']); ?>">Quay lại</a>
    </div>
  </div>

  <script>
    window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 300); });
  </script>
</body>
</html>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
