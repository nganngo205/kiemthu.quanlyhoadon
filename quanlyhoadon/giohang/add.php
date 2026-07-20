<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/functions.php';


if (!isset($_SESSION['cart_id'])) {
    $maxRes = $conn->query("SELECT MAX(CAST(id AS UNSIGNED)) AS maxid FROM cart WHERE id REGEXP '^[0-9]+$'");
    $row = $maxRes ? $maxRes->fetch_assoc() : null;
    $nextInt = 1;
    if ($row && $row['maxid']) {
        $nextInt = intval($row['maxid']) + 1;
        if ($nextInt <= 0) $nextInt = 1;
    }
    if ($nextInt > 99999999) {
        $nextInt = intval(time());
    }
    $cart_id = str_pad((string)$nextInt, 8, '0', STR_PAD_LEFT);
    $status = 'pending';
    $stmt = $conn->prepare('INSERT INTO cart (id, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = status');
    $stmt->bind_param('ss', $cart_id, $status);
    if (!$stmt->execute()) {
        $msg = 'Lỗi tạo giỏ';
        if (is_ajax()) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'msg'=>$msg]); exit; }
        die(htmlspecialchars($msg . ': ' . $conn->error));
    }
    $_SESSION['cart_id'] = $cart_id;
} else {
    $cart_id = $_SESSION['cart_id'];
}

$product_id = trim($_POST['product_id'] ?? '');
$barcode = trim($_POST['barcode'] ?? '');
$quantity = $_POST['quantity'] ?? '';


    if (!is_numeric($quantity) || intval($quantity) <= 0) {
    $msg = 'Số lượng không hợp lệ';
    if (is_ajax()) json_error($msg);
    redirect_with_msg('view.php', $msg, 'danger');
}
$quantity = intval($quantity);

// Tìm sản phẩm
$prod = null;
if ($product_id !== '') {
    $stmt = $conn->prepare('SELECT id, name, price, quantity FROM product WHERE LOWER(id)=LOWER(?)');
    $stmt->bind_param('s', $product_id);
    $stmt->execute(); 
    $res = $stmt->get_result(); 
    $prod = $res->fetch_assoc();
}

if (!$prod && $barcode !== '') {
    $stmt = $conn->prepare('SELECT id, name, price, quantity FROM product WHERE barcode=?');
    $stmt->bind_param('s', $barcode);
    $stmt->execute(); 
    $res = $stmt->get_result(); 
    $prod = $res->fetch_assoc();
}

    if (!$prod) {
    $msg = 'Không có sản phẩm';
    if (is_ajax()) json_error($msg);
    redirect_with_msg('view.php', $msg, 'danger');
}

// kiểm tra tồn
    if ($prod['quantity'] <= 0) {
    $msg = 'Hết hàng';
    if (is_ajax()) json_error($msg);
    redirect_with_msg('view.php', $msg, 'danger');
}

if ($quantity > $prod['quantity']) {
    $msg = 'Không đủ tồn kho';
    if (is_ajax()) json_error($msg);
    redirect_with_msg('view.php', $msg, 'danger');
}

// đảm bảo bản ghi cart tồn tại
$ensure = $conn->prepare('INSERT INTO cart (id, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = status');
$st_status = 'pending';
$ensure->bind_param('ss', $cart_id, $st_status);
if (!$ensure->execute()) {
    $msg = 'Lỗi đảm bảo giỏ hàng';
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'msg'=>$msg]); exit; }
    header('Location: view.php?msg=' . urlencode($msg) . '&status=danger'); exit;
}

// Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
$stmt = $conn->prepare('SELECT quantity FROM cart_item WHERE cart_id = ? AND product_id = ?');
$stmt->bind_param('ss', $cart_id, $prod['id']);
$stmt->execute();
$res = $stmt->get_result();
$existing_item = $res->fetch_assoc();

    if ($existing_item) {
    // Cập nhật số lượng (cộng dồn)
    $new_quantity = $existing_item['quantity'] + $quantity;
    if ($new_quantity > $prod['quantity']) {
        $msg = 'Không đủ tồn kho';
        if (is_ajax()) json_error($msg);
        redirect_with_msg('view.php', $msg, 'danger');
    }
    $stmt = $conn->prepare('UPDATE cart_item SET quantity = ?, price = ? WHERE cart_id = ? AND product_id = ?');
    $price = $prod['price'] * $new_quantity;
    $stmt->bind_param('idss', $new_quantity, $price, $cart_id, $prod['id']);
    if (!$stmt->execute()) {
        $msg = 'Lỗi cập nhật giỏ';
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'msg'=>$msg]); exit; }
        header('Location: view.php?msg=' . urlencode($msg) . '&status=danger'); exit;
    }
    $msg = 'Sản phẩm đã có → cộng dồn số lượng';
    // cảnh báo sắp hết hàng
    if ($prod['quantity'] - $new_quantity <= 5) $msg .= '. Cảnh báo sắp hết hàng';
    if (is_ajax()) json_success($msg);
    redirect_with_msg('view.php', $msg, 'success');
} else {
    // Thêm mới vào giỏ hàng
    $stmt = $conn->prepare('INSERT INTO cart_item (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    $price = $prod['price'] * $quantity;
    $stmt->bind_param('ssid', $cart_id, $prod['id'], $quantity, $price);
    if (!$stmt->execute()) {
        $msg = 'Lỗi thêm vào giỏ';
        if (is_ajax()) json_error($msg);
        redirect_with_msg('view.php', $msg, 'danger');
    }
    $msg = 'Thêm vào giỏ thành công';
    if ($prod['quantity'] - $quantity <= 5) $msg .= '. Cảnh báo sắp hết hàng';
    if (is_ajax()) json_success($msg);
    redirect_with_msg('view.php', $msg, 'success');
}
?>
