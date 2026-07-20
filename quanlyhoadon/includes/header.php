<?php
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý thanh toán - Sản phẩm</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/quanlyhoadon/assets/css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100" style="font-family: 'Poppins', sans-serif;">
<nav class="navbar navbar-expand-lg" style="background: linear-gradient(90deg, #4e73df 0%, #3753c8 100%);">
  <div class="container-fluid">
    <a class="navbar-brand text-white d-flex align-items-center" href="/quanlyhoadon/index.php">
      <span class="badge bg-white text-primary me-2" style="font-weight:600;">QL</span>
      <span style="font-weight:600;">Quản lý thanh toán</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="/quanlyhoadon/sanpham/list.php">Sản phẩm</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/quanlyhoadon/giohang/view.php">Giỏ hàng</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/quanlyhoadon/thanhtoan/pay.php">Thanh toán</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/quanlyhoadon/baocao/report.php">Báo cáo</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">
  <div class="row mb-3">
    <div class="col-12">
      <div class="p-4 rounded-3" style="background: linear-gradient(180deg, rgba(78,115,223,0.06), rgba(255,255,255,0.6));">
        <h1 class="h3 mb-1">Quản lý thanh toán</h1>
        <p class="text-muted mb-0">Giao diện quản lý bán hàng — sản phẩm, giỏ hàng, thanh toán và báo cáo.</p>
      </div>
    </div>
  </div>
