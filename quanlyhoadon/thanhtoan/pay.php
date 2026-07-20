<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['cart_id'])) {
  // khi giỏ hàng rỗng, chuyển về trang danh sách sản phẩm trong thư mục dự án
  redirect_with_msg('/quanlyhoadon/sanpham/list.php', 'Giỏ hàng rỗng', 'danger');
}
$cart_id = $_SESSION['cart_id'];

// Lấy các item từ DB
$stmt = $conn->prepare('SELECT ci.product_id, ci.quantity, ci.price AS subtotal, p.name, p.price AS unit_price FROM cart_item ci JOIN product p ON ci.product_id = p.id WHERE ci.cart_id = ?');
$stmt->bind_param('s', $cart_id);
$stmt->execute();
$res = $stmt->get_result();
$cart_items = $res->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $it) {
  $total += (float)$it['subtotal'];
}

$errors = [];
$isAjax = is_ajax();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $method = $_POST['method'] ?? 'TIEN_MAT';
    if ($method === 'TIEN_MAT') {
    $paid = floatval($_POST['paid'] ?? 0);
    if ($paid < $total) {
      $errors[] = 'Thanh toán không đủ';
    } else {
        // Tự động tạo ID hóa đơn theo định dạng INVyyyyMMdd
        $base = 'INV' . date('Ymd');
        // tìm xem đã có id bắt đầu bằng base chưa, để đảm bảo duy nhất
        $q = $conn->prepare("SELECT id FROM invoice WHERE id LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1");
        $q->bind_param('s', $base);
        $q->execute();
        $r = $q->get_result()->fetch_assoc();
        if (!$r) {
            $invoiceId = $base; 
        } else {
            // nếu tồn tại, thử tìm suffix numeric (nếu có) và tăng
            $existing = $r['id'];
            if ($existing === $base) {
                $invoiceId = $base . '_1';
            } else {
                $suffix = substr($existing, strlen($base));
                if (preg_match('/^_(\d+)$/', $suffix, $m)) {
                    $next = intval($m[1]) + 1;
                    $invoiceId = $base . '_' . $next;
                } else {
                    $invoiceId = $base . '_' . uniqid();
                }
            }
        }

    $conn->begin_transaction();
    try {
    $stmt = $conn->prepare('INSERT INTO invoice (id, session_id, total, method, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $status = 'Đã thanh toán';
    $now = date('Y-m-d H:i:s');
    $sid = $cart_id; 
    $stmt->bind_param('ssdsss', $invoiceId, $sid, $total, $method, $status, $now);
    if (!$stmt->execute()) throw new Exception('Lỗi tạo hóa đơn');

        $stmt_item = $conn->prepare('INSERT INTO invoice_item (invoice_id, product_id, name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
        $u = $conn->prepare('UPDATE product SET quantity = quantity - ? WHERE LOWER(id)=LOWER(?)');
        $check = $conn->prepare('SELECT quantity FROM product WHERE LOWER(id)=LOWER(?) FOR UPDATE');
        foreach ($cart_items as $it) {
          $unit = (float)$it['unit_price'];
          $qty = (int)$it['quantity'];
          $subtotal = (float)$it['subtotal'];

          $check->bind_param('s', $it['product_id']);
          $check->execute();
          $r = $check->get_result()->fetch_assoc();
          if (!$r) throw new Exception('Sản phẩm không tồn tại');
          if ($r['quantity'] < $qty) throw new Exception('Không đủ tồn kho');

                    if (!$stmt_item->bind_param('sssidi', $invoiceId, $it['product_id'], $it['name'], $unit, $qty, $subtotal)) throw new Exception('Lỗi dữ liệu hóa đơn');
                    if (!$stmt_item->execute()) throw new Exception('Lỗi thêm chi tiết hóa đơn');
                    if (!$u->bind_param('is', $qty, $it['product_id'])) throw new Exception('Lỗi cập nhật tồn');
                    if (!$u->execute()) throw new Exception('Lỗi cập nhật tồn kho');
        }

        $c = $conn->prepare('UPDATE cart SET status = ? WHERE id = ?');
        $st = 'completed';
        $c->bind_param('ss', $st, $cart_id);
        if (!$c->execute()) throw new Exception('Lỗi cập nhật giỏ');
        $conn->commit();
        unset($_SESSION['cart_id']);

  $change = $paid - $total;
  $msg = 'Thanh toán thành công. Mã: '.$invoiceId.' Tiền thừa: '.number_format($change);
  if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'msg'=>$msg,'invoice'=>$invoiceId]); exit; }
  header('Location: /quanlyhoadon/baocao/invoice_view.php?id=' . urlencode($invoiceId));
        exit;
      } catch (Exception $ex) {
        $conn->rollback();
        $errors[] = $ex->getMessage();
      }
    }
  } else {
    $success = true;
      if ($success) {
      $conn->begin_transaction();
      try {
        $base = 'INV' . date('Ymd');
        $q = $conn->prepare("SELECT id FROM invoice WHERE id LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1");
        $q->bind_param('s', $base);
        $q->execute();
        $r = $q->get_result()->fetch_assoc();
        if (!$r) $invoiceId = $base; else {
            $existing = $r['id'];
            if ($existing === $base) $invoiceId = $base . '_1';
            else {
                $suffix = substr($existing, strlen($base));
                if (preg_match('/^_(\d+)$/', $suffix, $m)) $invoiceId = $base . '_' . (intval($m[1]) + 1);
                else $invoiceId = $base . '_' . uniqid();
            }
        }
        $stmt = $conn->prepare('INSERT INTO invoice (id, session_id, total, method, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $status = 'Đã thanh toán';
        $now = date('Y-m-d H:i:s');
        $sid = $cart_id;
        $stmt->bind_param('ssdsss', $invoiceId, $sid, $total, $method, $status, $now);
        if (!$stmt->execute()) throw new Exception('Lỗi tạo hóa đơn');

        $stmt_item = $conn->prepare('INSERT INTO invoice_item (invoice_id, product_id, name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
        $u = $conn->prepare('UPDATE product SET quantity = quantity - ? WHERE LOWER(id)=LOWER(?)');
        $check = $conn->prepare('SELECT quantity FROM product WHERE LOWER(id)=LOWER(?) FOR UPDATE');
        foreach ($cart_items as $it) {
          $unit = (float)$it['unit_price'];
          $qty = (int)$it['quantity'];
          $subtotal = (float)$it['subtotal'];
          $check->bind_param('s', $it['product_id']);
          $check->execute();
          $r = $check->get_result()->fetch_assoc();
          if (!$r) throw new Exception('Sản phẩm không tồn tại');
          if ($r['quantity'] < $qty) throw new Exception('Không đủ tồn kho');

          if (!$stmt_item->bind_param('sssidi', $invoiceId, $it['product_id'], $it['name'], $unit, $qty, $subtotal)) throw new Exception('Lỗi dữ liệu hóa đơn');
          if (!$stmt_item->execute()) throw new Exception('Lỗi thêm chi tiết hóa đơn');
          if (!$u->bind_param('is', $qty, $it['product_id'])) throw new Exception('Lỗi cập nhật tồn kho');
          if (!$u->execute()) throw new Exception('Lỗi cập nhật tồn kho');
        }
        $c = $conn->prepare('UPDATE cart SET status = ? WHERE id = ?');
        $st = 'completed';
        $c->bind_param('ss', $st, $cart_id);
        if (!$c->execute()) throw new Exception('Lỗi cập nhật giỏ');
        $conn->commit();
        unset($_SESSION['cart_id']);
        $msg = 'Thanh toán điện tử thành công. Mã: '.$invoiceId;
  if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'msg'=>$msg,'invoice'=>$invoiceId]); exit; }
  header('Location: /quanlyhoadon/baocao/invoice_view.php?id=' . urlencode($invoiceId));
        exit;
      } catch (Exception $ex) {
        $conn->rollback();
        $errors[] = $ex->getMessage();
      }
    } else {
      $errors[] = 'Giao dịch thất bại hoặc bị hủy';
    }
  }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="row">
  <div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3">Thanh toán</h4>
        <?php if ($errors): ?>
          <div class="alert alert-danger"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>
        <div class="mb-3">Tổng tiền: <strong><?php echo number_format($total); ?> VNĐ</strong></div>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Phương thức</label>
            <select name="method" id="methodSelect" class="form-select">
              <option value="TIEN_MAT">Tiền mặt</option>
              <option value="THE">Thẻ</option>
              <option value="QR">QR</option>
              <option value="VI">Ví điện tử</option>
            </select>
          </div>
          <div class="mb-3" id="cashInput">
            <label class="form-label">Số tiền khách trả</label>
            <input name="paid" type="number" step="0.01" class="form-control" placeholder="Nhập số tiền khách trả">
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Xác nhận thanh toán</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// show/hide cash 
document.getElementById('methodSelect').addEventListener('change', function(){
  var cash = document.getElementById('cashInput');
  if (this.value === 'TIEN_MAT') cash.style.display = '';
  else cash.style.display = 'none';
});
</script>
