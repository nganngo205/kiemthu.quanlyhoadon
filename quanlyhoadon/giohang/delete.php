<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
$isAjax = is_ajax();
$pid = $_GET['id'] ?? null;
if (!isset($_SESSION['cart_id'])) { 
    if ($isAjax) json_error('Mã giỏ hàng không hợp lệ');
    redirect_with_msg('view.php', 'Mã giỏ hàng không hợp lệ', 'danger');
}
$cart_id = $_SESSION['cart_id'];
if ($pid) {
    $d = $conn->prepare('DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?');
    $d->bind_param('ss', $cart_id, $pid);
    $d->execute();
}
if ($isAjax) json_success('Xóa thành công');
redirect_with_msg('view.php', 'Xóa thành công', 'success');
?>
