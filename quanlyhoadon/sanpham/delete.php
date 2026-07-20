<?php
$requirePath = __DIR__ . '/../db_connect.php';
require_once $requirePath;
require_once __DIR__ . '/../includes/functions.php';
$id = $_GET['id'] ?? null;
if (!$id) { redirect_with_msg('list.php', 'ID không tồn tại', 'danger'); }
$stmt = $conn->prepare('SELECT quantity FROM product WHERE LOWER(id)=LOWER(?)');
$stmt->bind_param('s', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
    json_error('ID không tồn tại');
}
if ($row['quantity'] > 0) {
    json_error('Không thể xóa khi còn tồn hàng');
}
$d = $conn->prepare('DELETE FROM product WHERE LOWER(id)=LOWER(?)');
$d->bind_param('s', $id);
$d->execute();
$msg = 'Xóa sản phẩm thành công trong hệ thống';
if (is_ajax()) json_success($msg);
redirect_with_msg('list.php', $msg, 'success');
?>
