<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    // validation (map messages to spec)
    // ID phải gồm 2 chữ cái in hoa + 6 chữ số, ví dụ: AB123456
    if ($id === '' || !preg_match('/^[A-Z]{2}\d{6}$/', $id)) $errors[] = 'ID không hợp lệ (2 chữ cái in hoa + 6 số)';
    // Barcode sẽ được tự động tạo, không cần validate
    $barcode = ''; // sẽ được gán sau
    if ($name === '' || strlen($name) > 100) $errors[] = 'Tên không hợp lệ';

    // price should be a positive number
    if (!is_numeric($price) || floatval($price) <= 0) {
        $errors[] = 'Giá tiền không hợp lệ';
    } else {
        $price = floatval($price);
    }

    // quantity should be integer > 0
    if (!is_numeric($quantity) || intval($quantity) <= 0) {
        $errors[] = 'Số lượng phải > 0';
    } else {
        $quantity = intval($quantity);
    }

    if (empty($errors)) {
        // kiểm tra tồn tại id/barcode (case-insensitive id)
        $stmt = $conn->prepare('SELECT id FROM product WHERE LOWER(id)=LOWER(?) OR barcode=?');
        $stmt->bind_param('ss', $id, $barcode);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'ID/Barcode đã tồn tại';
        } else {
            // Insert with empty barcode first to get auto increment value
            $stmt2 = $conn->prepare('INSERT INTO product (id, barcode, name, price, quantity) VALUES (?, "", ?, ?, ?)');
            $stmt2->bind_param('ssdi', $id, $name, $price, $quantity);
            
            if ($stmt2->execute()) {
                // Get the auto increment value and update barcode
                $seq = $conn->query("SELECT barcode_seq FROM product WHERE id = '$id'")->fetch_assoc()['barcode_seq'];
                $barcode = str_pad($seq, 8, '0', STR_PAD_LEFT);
                
                // Update barcode
                $stmt3 = $conn->prepare('UPDATE product SET barcode = ? WHERE id = ?');
                $stmt3->bind_param('ss', $barcode, $id);
                
                if ($stmt3->execute()) {
                    // success
                    $msg = 'Thêm mới thành công';
                    if (is_ajax()) json_success($msg);
                    redirect_with_msg('list.php', $msg, 'success');
                } else {
                    $errors[] = 'Lỗi cập nhật barcode';
                }
            } else {
                $errors[] = 'Lỗi thêm sản phẩm';
            }
        }
    }

    // If AJAX and there are errors, return JSON
    if (is_ajax() && !empty($errors)) {
        json_error(implode('; ', $errors));
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Thêm sản phẩm</title></head>
<body>
    <h2>Thêm sản phẩm</h2>
    <p><a href="list.php">Quay lại danh sách</a></p>
    <?php if (!empty($errors)): ?>
        <div style="color:red;"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
    <?php endif; ?>
    <form method="post">
        <label>ID sản phẩm (2 chữ cái in hoa + 6 số): <input name="id" pattern="[A-Z]{2}\d{6}" required title="ID phải gồm 2 chữ cái in hoa và 6 số, ví dụ: AB123456"></label><br>
        <label>Tên sản phẩm: <input name="name" required></label><br>
    <label>Giá (VNĐ): <input name="price" type="number" step="1000" min="1000" required></label><br>
    <label>Số lượng: <input name="quantity" type="number" min="1" required></label><br>
        <button type="submit">Lưu</button>
    </form>
</body></html>

