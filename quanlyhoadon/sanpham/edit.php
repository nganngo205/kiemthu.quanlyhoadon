<?php
$requirePath = __DIR__ . '/../db_connect.php';
require_once $requirePath;
require_once __DIR__ . '/../includes/functions.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: list.php'); exit; }
$stmt = $conn->prepare('SELECT id, barcode, name, price, quantity FROM product WHERE LOWER(id)=LOWER(?)');
$stmt->bind_param('s', $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
if (!$product) { echo 'Không tìm thấy sản phẩm'; exit; }
 $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if ($name === '' || strlen($name) > 100) $errors[] = 'Tên không hợp lệ';
    if (!is_numeric($price) || floatval($price) <= 0) $errors[] = 'Giá tiền không hợp lệ'; else $price = floatval($price);
    if (!is_numeric($quantity) || intval($quantity) <= 0) $errors[] = 'Số lượng phải > 0'; else $quantity = intval($quantity);

    if (empty($errors)) {
        $u = $conn->prepare('UPDATE product SET name=?, price=?, quantity=? WHERE LOWER(id)=LOWER(?)');
        $u->bind_param('sdis', $name, $price, $quantity, $id);
        if ($u->execute()) {
            $msg = 'Cập nhật thành công';
            if (is_ajax()) json_success($msg);
            redirect_with_msg('list.php', $msg, 'success');
        } else {
            $errors[] = 'Lỗi cập nhật: '.$conn->error;
        }
    }

    if (is_ajax() && !empty($errors)) {
        json_error(implode('; ', $errors));
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Sửa sản phẩm</title></head>
<body>
    <h2>Sửa sản phẩm <?php echo htmlspecialchars($product['id']); ?></h2>
    <p><a href="list.php">Quay lại</a></p>
    <?php if ($errors): ?><div style="color:red;"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
    <form method="post">
        <div>ID: <?php echo htmlspecialchars($product['id']); ?></div>
        <div>Barcode: <?php echo htmlspecialchars($product['barcode']); ?></div>
        <label>Tên: <input name="name" value="<?php echo htmlspecialchars($product['name']); ?>"></label><br>
        <label>Giá: <input name="price" type="number" step="1000" min="1000" value="<?php echo htmlspecialchars($product['price']); ?>"></label><br>
    <label>Số lượng: <input name="quantity" type="number" min="1" value="<?php echo htmlspecialchars($product['quantity']); ?>"></label><br>
        <button type="submit">Cập nhật</button>
    </form>
</body></html>
