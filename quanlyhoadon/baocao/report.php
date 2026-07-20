<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../includes/header.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$report = null;
$rows = [];
if ($from && $to) {
        $dfrom = DateTime::createFromFormat('Y-m-d', $from);
        $dto = DateTime::createFromFormat('Y-m-d', $to);
    if (!$dfrom || !$dto) { $error = 'Lỗi định dạng hoặc ngày không tồn tại'; }
        else {
                $fromS = $dfrom->format('Y-m-d').' 00:00:00';
                $toS = $dto->format('Y-m-d').' 23:59:59';
                $stmt = $conn->prepare('SELECT * FROM invoice WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC');
                $stmt->bind_param('ss', $fromS, $toS);
                $stmt->execute(); $res = $stmt->get_result();
                while($r = $res->fetch_assoc()) {
                        $rows[] = $r;
                }
                $total = 0; foreach($rows as $r) $total += $r['total'];
                $report = ['count' => count($rows), 'total' => $total];
        }
}

 
if (isset($_GET['export']) && $_GET['export']==='csv' && $report) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=report.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','session_id','total','method','created_at']);
        foreach($rows as $r) fputcsv($out, [$r['id'],$r['session_id'],$r['total'],$r['method'],$r['created_at']]);
        fclose($out);
        exit;
}
?>

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Báo cáo doanh thu</h4>
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Từ</label>
                        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Đến</label>
                        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary">Xem</button>
                        <?php if ($report): ?>
                            <a class="btn btn-outline-secondary ms-2" href="?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&export=csv">Xuất CSV</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (isset($error)): ?><div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

                <?php if ($report): ?>
                    <div class="mt-3 mb-2">Kết quả: <strong><?php echo $report['count']; ?></strong> hoá đơn — Tổng: <strong><?php echo number_format($report['total']); ?> VNĐ</strong></div>
                    <div class="table-responsive">
                            <table class="table table-striped">
                                <thead><tr><th>Mã hoá đơn</th><th>Mã phiên</th><th>Tổng</th><th>Phương thức</th><th>Ngày</th><th>Trạng thái hóa đơn</th></tr></thead>
                                <tbody>
                                    <?php foreach($rows as $r): ?>
                                    <tr>
                                        <td><a href="invoice_view.php?id=<?php echo urlencode($r['id']); ?>"><?php echo htmlspecialchars($r['id']); ?></a></td>
                                        <td><?php echo htmlspecialchars($r['session_id']); ?></td>
                                        <td><?php echo number_format($r['total']); ?></td>
                                        <td><?php echo htmlspecialchars($r['method']); ?></td>
                                        <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($r['status'] === 'Đã thanh toán' ? 'Đã thanh toán' : 'Chưa thanh toán'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
