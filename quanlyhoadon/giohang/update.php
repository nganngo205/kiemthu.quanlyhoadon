<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
$isAjax = is_ajax();
$qtys = $_POST['qty'] ?? [];
if (!isset($_SESSION['cart_id'])) { 
    if ($isAjax) json_error('Mã giỏ hàng không hợp lệ');
    redirect_with_msg('view.php', 'Mã giỏ hàng không hợp lệ', 'danger');
}
$cart_id = $_SESSION['cart_id'];
foreach ($qtys as $pid => $q) {
    // Đảm bảo số lượng là số nguyên và lớn hơn 0
    $q = filter_var($q, FILTER_VALIDATE_INT);
    if ($q === false || $q <= 0) {
        if ($isAjax) {
            json_error('Số lượng phải là số nguyên dương');
            exit;
        }
        redirect_with_msg('view.php', 'Số lượng phải là số nguyên dương', 'danger');
    }
    // kiểm tra tồn kho
    $stmt = $conn->prepare('SELECT quantity, price FROM product WHERE LOWER(id)=LOWER(?)');
    $stmt->bind_param('s', $pid);
    $stmt->execute(); $res = $stmt->get_result(); $prod = $res->fetch_assoc();
    if (!$prod) {
        $d = $conn->prepare('DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?');
        $d->bind_param('ss', $cart_id, $pid);
        $d->execute();
        continue;
    }
    if ($q > $prod['quantity']) { 
        // không đủ tồn kho -> set to available and report
        $q = $prod['quantity'];
        if ($isAjax) json_error('Không đủ tồn kho');
    }
    $subtotal = $prod['price'] * $q;
    $u = $conn->prepare('UPDATE cart_item SET quantity = ?, price = ? WHERE cart_id = ? AND product_id = ?');
    $u->bind_param('idss', $q, $subtotal, $cart_id, $pid);
    $u->execute();
}
if ($isAjax) json_success('Cập nhật thành công');
redirect_with_msg('view.php', 'Cập nhật thành công', 'success');
?>
