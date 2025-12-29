<?php
session_start();
include 'config.php';

// Proses checkout (pengurangan stok otomatis + validasi stok)
if (isset($_POST['process_checkout'])) {
    $orderData = json_decode($_POST['order_data'], true);
    $orderNumber = $_POST['order_number'];
    $paymentMethod = $_POST['payment_method'];
    $total = $_POST['total'];
    $customerName = $_POST['customer_name']; // 🧩 Tambahan
    $namaProduk = $_POST['nama_produk'];

    // Ambil semua nama produk dari orderData
$namaProdukList = array_column($orderData, 'name');
$namaProdukGabung = implode(', ', $namaProdukList);

// Simpan transaksi lengkap dengan daftar produk
mysqli_query($conn, "INSERT INTO transaksi (order_number, customer_name, nama_produk, payment_method, total, tanggal)
                     VALUES ('$orderNumber', '$customerName', '$namaProdukGabung', '$paymentMethod', '$total', NOW())");


    foreach ($orderData as $item) {
        $productName = mysqli_real_escape_string($conn, $item['name']);
        $qty = (int)$item['qty'];



        // === 1. Cek stok dulu ===
        $cek = mysqli_query($conn, "SELECT stok FROM produk WHERE nama = '$productName'");
        $data = mysqli_fetch_assoc($cek);

        // Jika produk tidak ditemukan
        if (!$data) {
            echo json_encode([
                'success' => false,
                'message' => "Produk '$productName' tidak ditemukan di database!"
            ]);
            exit;
        }

        // Jika stok kosong atau tidak cukup
        if ($data['stok'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => "Stok '$productName' habis! Tidak bisa melakukan transaksi."
            ]);
            exit;
        }

        if ($data['stok'] < $qty) {
            echo json_encode([
                'success' => false,
                'message' => "Stok '$productName' hanya tersisa {$data['stok']} unit, tidak cukup untuk pesanan $qty!"
            ]);
            exit;
        }

        // === 2. Jika stok cukup, kurangi stok ===
        mysqli_query($conn, "
            UPDATE produk 
            SET stok = GREATEST(stok - $qty, 0) 
            WHERE nama = '$productName'
        ");
    }

    // === 3. Jika semua sukses ===
    echo json_encode([
        'success' => true,
        'message' => "Transaksi $orderNumber berhasil! Stok produk telah diperbarui."
    ]);
    exit;
}


// Get all categories from products
$categoryQuery = mysqli_query($conn, "SELECT DISTINCT kategori FROM produk ORDER BY kategori");
$categories = [['id' => 'all', 'name' => 'Semua Produk']];
while ($cat = mysqli_fetch_assoc($categoryQuery)) {
    $categories[] = ['id' => $cat['kategori'], 'name' => $cat['kategori']];
}

// Get all products from database
$productQuery = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
$products = [];
while ($row = mysqli_fetch_assoc($productQuery)) {
    $hargaAsli = $row['harga'];
    $diskon = $row['diskon'];
    $hargaDiskon = $hargaAsli - ($hargaAsli * $diskon / 100);
    
    $products[] = [
        'id' => $row['id'],
        'name' => $row['nama'],
        'price' => $hargaDiskon,
        'original_price' => $hargaAsli,
        'discount' => $diskon,
        'image' => $row['gambar'],
        'category' => $row['kategori'],
        'stock' => $row['stok'],
        'satuan' => $row['satuan'],
        'keterangan' => $row['keterangan']
    ];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kasir | Inventory System</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    .content-wrapper {
      margin-left: 250px;
      padding: 0 !important;
    }

    .pos-container {
      display: flex;
      height: calc(100vh - 57px);
    }

    .products-section {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: #f4f6f9;
    }

    .order-section {
      width: 400px;
      background: white;
      border-left: 2px solid #dee2e6;
      display: flex;
      flex-direction: column;
      transition: all 0.3s ease;
    }

    .order-section.hidden {
      display: none !important;
    }

    .category-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .category-btn {
      padding: 10px 20px;
      border: none;
      background: white;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s;
    }

    .category-btn:hover {
      background: #e9ecef;
    }

    .category-btn.active {
      background: #4a5568;
      color: white;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 15px;
    }

    .product-card {
      background: white;
      border-radius: 12px;
      padding: 15px;
      cursor: pointer;
      transition: all 0.3s;
      border: 2px solid transparent;
    }

    .product-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-color: #4a5568;
    }

    .product-image {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
      background: #f8f9fa;
      display: block;
    }

    .product-name {
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 5px;
      height: 36px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .product-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .product-price {
      font-size: 14px;
      font-weight: bold;
      color: #4a5568;
    }

    .product-stock {
      font-size: 12px;
      color: #6c757d;
    }

    .order-header {
      padding: 20px;
      border-bottom: 2px solid #e9ecef;
    }

    .order-items {
      flex: 1;
      overflow-y: auto;
      padding: 15px;
    }

    .order-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      background: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 10px;
    }

    .order-item-image {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 6px;
      background: white;
      display: block;
    }

    .order-item-info {
      flex: 1;
    }

    .order-item-name {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 3px;
    }

    .order-item-price {
      font-size: 12px;
      color: #6c757d;
    }

    .order-item-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .qty-btn {
      width: 28px;
      height: 28px;
      border: none;
      background: white;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .qty-btn:hover {
      background: #e9ecef;
    }

    .qty-display {
      font-weight: 600;
      min-width: 20px;
      text-align: center;
    }

    .order-summary {
      padding: 20px;
      border-top: 2px solid #e9ecef;
      background: #f8f9fa;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .summary-row.total {
      font-size: 18px;
      font-weight: bold;
      padding-top: 10px;
      border-top: 2px solid #dee2e6;
      margin-top: 10px;
    }

    .checkout-btn {
      width: 100%;
      padding: 15px;
      background: #4a5568;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 15px;
      transition: all 0.3s;
    }

    .checkout-btn:hover {
      background: #2d3748;
    }

    .checkout-btn:disabled {
      background: #cbd5e0;
      cursor: not-allowed;
    }

    .action-btn {
      flex: 1;
      padding: 12px;
      background: white;
      color: #4a5568;
      border: 2px solid #4a5568;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .action-btn:hover:not(:disabled) {
      background: #4a5568;
      color: white;
    }

    .action-btn:disabled {
      background: #f7fafc;
      border-color: #cbd5e0;
      color: #cbd5e0;
      cursor: not-allowed;
    }

    .d-flex.gap-2 {
      display: flex;
      gap: 8px;
    }

    .payment-methods {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .payment-option {
      display: flex;
      align-items: center;
      padding: 15px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
      background: #f8f9fa;
    }

    .payment-option:hover {
      border-color: #4a5568;
      background: white;
      transform: translateX(5px);
    }

    .payment-option.selected {
      border-color: #4299e1;
      background: #ebf8ff;
    }

    .payment-icon {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border-radius: 10px;
      margin-right: 15px;
      font-size: 24px;
      color: #4a5568;
    }

    .payment-option.selected .payment-icon {
      background: #4299e1;
      color: white;
    }

    .payment-info {
      flex: 1;
    }

    .payment-info h6 {
      margin: 0 0 3px 0;
      font-weight: 600;
      color: #2d3748;
    }

    .payment-info p {
      margin: 0;
      font-size: 12px;
      color: #718096;
    }

    .payment-check {
      font-size: 24px;
      color: #e2e8f0;
    }

    .payment-option.selected .payment-check {
      color: #4299e1;
    }

    .modal-content {
      border-radius: 15px;
      overflow: hidden;
    }

    .modal-header.bg-gradient-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .empty-order {
      text-align: center;
      padding: 40px 20px;
      color: #a0aec0;
    }

    .empty-order i {
      font-size: 48px;
      margin-bottom: 15px;
    }

    .delete-btn {
      color: #e53e3e;
      cursor: pointer;
      font-size: 18px;
    }

    .delete-btn:hover {
      color: #c53030;
    }

    @media (max-width: 992px) {
      .content-wrapper {
        margin-left: 0;
      }
      
      .order-section {
        position: fixed;
        right: -400px;
        top: 57px;
        height: calc(100vh - 57px);
        z-index: 1000;
        transition: right 0.3s;
        box-shadow: -5px 0 15px rgba(0,0,0,0.1);
      }

      .order-section.show {
        right: 0;
      }
    }
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="index.php" class="nav-link">Home</a></li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link text-center">
        <span class="brand-text font-weight-light">Kasir System</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <?php

                // Ambil role user
                $role = $_SESSION['role'] ?? '';

                // =============================
                // MENU UNTUK ADMIN
                // =============================
                if ($role == 'admin') { 
                ?>
                    <!-- Master Menu -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Kelola
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="user.php" class="nav-link"><i class="fas fa-users nav-icon"></i><p>User</p></a></li>
                            <li class="nav-item"><a href="produk.php" class="nav-link"><i class="fas fa-mobile-alt nav-icon"></i><p>Produk</p></a></li>
                            <li class="nav-item"><a href="kategori.php" class="nav-link"><i class="fas fa-layer-group nav-icon"></i><p>Kategori</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                    <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>

                <?php 
                // =============================
                // MENU UNTUK KASIR
                // =============================
                } elseif ($role == 'kasir') { 
                ?>
                    <!-- Transaksi Menu -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>
                                Transaksi
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="daftarproduk.php" class="nav-link"><i class="nav-icon fas fa-cash-register"></i><p>Penjualan</p></a></li>
                            <li class="nav-item"><a href="detailPenjualan.php" class="nav-link"><i class="nav-icon fas fa-receipt"></i><p>Detail Penjualan</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                    <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>

                <?php 
                // =============================
                // MENU UNTUK OWNER
                // =============================
                } elseif ($role == 'owner') { 
                ?>
                    <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                    <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>
                <?php 
                } 
                ?>

                <!-- Tombol Logout (muncul di semua role) -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>


  <!-- Content -->
  <div class="content-wrapper">
    <div class="pos-container">
      
      <!-- Products Section -->
      <div class="products-section">
        <div class="category-tabs">
          <?php foreach ($categories as $index => $cat): ?>
            <button class="category-btn <?= $index == 0 ? 'active' : '' ?>" 
                    data-category="<?= htmlspecialchars($cat['id']) ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </button>
          <?php endforeach; ?>
        </div>

        <div class="products-grid">
          <?php foreach ($products as $product): 
            $imgPath = !empty($product['image']) ? "uploads/{$product['image']}" : "https://via.placeholder.com/200x120?text=No+Image";
          ?>
            <div class="product-card" 
                 data-category="<?= htmlspecialchars($product['category']) ?>"
                 data-product='<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
              <?php if ($product['discount'] > 0): ?>
                <div style="position: absolute; top: 8px; right: 8px; background: #e53e3e; color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                  -<?= $product['discount'] ?>%
                </div>
              <?php endif; ?>
              
              <img src="<?= $imgPath ?>" class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
              
              <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
              
              <?php if ($product['discount'] > 0): ?>
                <div style="text-decoration: line-through; color: #999; font-size: 11px; margin-bottom: 2px;">
                  Rp<?= number_format($product['original_price'], 0, ',', '.') ?>
                </div>
              <?php endif; ?>
              
              <div class="product-footer">
                <span class="product-price">Rp<?= number_format($product['price'], 0, ',', '.') ?></span>
                <span class="product-stock">
                  <i class="fas fa-box"></i> <?= $product['stock'] ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Order Section -->
      <div class="order-section" id="orderSection" style="display: none;">
        <div class="order-header">
          <h5 class="mb-1"><strong>Order List</strong></h5>
          <small class="text-muted" id="orderNumber"></small>
        </div>

        <div class="order-items" id="orderItems">
          <div class="empty-order">
            <i class="fas fa-shopping-basket"></i>
            <p>Belum ada item</p>
          </div>
        </div>

        <div class="order-summary">
          <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal">Rp0</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span id="total">Rp0</span>
          </div>
          
          <input type="text" id="customer_name" class="form-control mt-2" placeholder="Nama Pelanggan">

          <!-- 🔹 Tombol Metode Pembayaran dipindah ke atas checkout -->
          <div class="d-flex gap-2 mt-2">
            <button class="action-btn" id="paymentBtn" disabled>
              <i class="fas fa-credit-card"></i> Metode Pembayaran
            </button>
          </div>

          <!-- 🔹 Checkout tetap di bawah -->
          <button class="checkout-btn" id="checkoutBtn">
            <i class="fas fa-check-circle"></i> Checkout
          </button>
        </div>

      </div>

    </div>
  </div>

  <footer class="main-footer text-center">
    <strong>&copy; 2025 Inventory System Handphone</strong> — M Eka Miharja
  </footer>
</div>

<!-- Modal Payment Method -->
<div class="modal fade" id="modalPayment" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title"><i class="fas fa-credit-card mr-2"></i> Pilih Metode Pembayaran</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4">
        <div class="payment-methods">
          <div class="payment-option" data-method="Tunai">
            <div class="payment-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="payment-info">
              <h6>Tunai</h6>
              <p>Pembayaran dengan uang cash</p>
            </div>
            <div class="payment-check">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          
          <div class="payment-option" data-method="Kartu Debit">
            <div class="payment-icon">
              <i class="fas fa-credit-card"></i>
            </div>
            <div class="payment-info">
              <h6>Kartu Debit</h6>
              <p>Pembayaran dengan kartu debit</p>
            </div>
            <div class="payment-check">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          
          <div class="payment-option" data-method="Kartu Kredit">
            <div class="payment-icon">
              <i class="fas fa-credit-card"></i>
            </div>
            <div class="payment-info">
              <h6>Kartu Kredit</h6>
              <p>Pembayaran dengan kartu kredit</p>
            </div>
            <div class="payment-check">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          
          <div class="payment-option" data-method="E-Wallet">
            <div class="payment-icon">
              <i class="fas fa-mobile-alt"></i>
            </div>
            <div class="payment-info">
              <h6>E-Wallet</h6>
              <p>GoPay, OVO, Dana, dll</p>
            </div>
            <div class="payment-check">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          
          <div class="payment-option" data-method="Transfer Bank">
            <div class="payment-icon">
              <i class="fas fa-university"></i>
            </div>
            <div class="payment-info">
              <h6>Transfer Bank</h6>
              <p>Transfer antar bank</p>
            </div>
            <div class="payment-check">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="button" class="btn btn-primary" id="confirmPaymentBtn" disabled>
          <i class="fas fa-check mr-1"></i> Konfirmasi
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
let orderItems = [];
let orderNumber = '';
let selectedPaymentMethod = '';

// Generate unique order number
function generateOrderNumber() {
  const date = new Date();
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
  
  return `#ORD${year}${month}${day}${hours}${minutes}${seconds}${random}`;
}

// Add product to order
$('.product-card').click(function() {
  const product = $(this).data('product');

  // tambahkan ini untuk debugging
  console.log('Produk diklik:', product);

  if (orderItems.length === 0) {
    orderNumber = generateOrderNumber();
    $('#orderNumber').text(orderNumber);
    $('#orderSection').fadeIn(300);
  }

  const existingItem = orderItems.find(item => item.id === product.id);

  if (existingItem) {
    existingItem.qty++;
  } else {
    orderItems.push({
      ...product,
      qty: 1
    });
  }

  renderOrder();
});


// Render order list
function renderOrder() {
  const container = $('#orderItems');
  
  if (orderItems.length === 0) {
    $('#orderSection').fadeOut(300);
    $('#checkoutBtn').prop('disabled', true);
    $('#paymentBtn').prop('disabled', true);
    $('#printBtn').prop('disabled', true);
    $('#cartCount').text(0);
    orderNumber = '';
  } else {
    let html = '';
    orderItems.forEach((item, index) => {
      const imgPath = item.image ? `uploads/${item.image}` : 'https://via.placeholder.com/50?text=No+Image';
      html += `
        <div class="order-item">
          <img src="${imgPath}" class="order-item-image" alt="${item.name}">
          <div class="order-item-info">
            <div class="order-item-name">${item.name}</div>
            <div class="order-item-price">Rp${formatNumber(item.price)}</div>
          </div>
          <div class="order-item-controls">
            <button class="qty-btn" onclick="decreaseQty(${index})">
              <i class="fas fa-minus"></i>
            </button>
            <span class="qty-display">${item.qty}</span>
            <button class="qty-btn" onclick="increaseQty(${index})">
              <i class="fas fa-plus"></i>
            </button>
            <i class="fas fa-trash delete-btn" onclick="removeItem(${index})"></i>
          </div>
        </div>
      `;
    });
    container.html(html);
    $('#checkoutBtn').prop('disabled', false);
    $('#paymentBtn').prop('disabled', false);
    $('#printBtn').prop('disabled', false);
    
    const totalItems = orderItems.reduce((sum, item) => sum + item.qty, 0);
    $('#cartCount').text(totalItems);
  }
  
  updateSummary();
}

// Increase quantity
function increaseQty(index) {
  const item = orderItems[index];
  const product = $('.product-card').eq(index).data('product');
  
  if (item.qty < item.stock) {
    item.qty++;
    renderOrder();
  } else {
    alert('Stok tidak mencukupi!');
  }
}

// Decrease quantity
function decreaseQty(index) {
  if (orderItems[index].qty > 1) {
    orderItems[index].qty--;
    renderOrder();
  }
}

// Remove item
function removeItem(index) {
  orderItems.splice(index, 1);
  renderOrder();
}

// Update summary
function updateSummary() {
  const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
  $('#subtotal').text('Rp' + formatNumber(subtotal));
  $('#total').text('Rp' + formatNumber(subtotal));
}

// Format number
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Checkout
$('#checkoutBtn').click(function() {
  console.log('Checkout button diklik'); // 🟢 Cek apakah fungsi terpanggil
  const namaProduk = orderItems.map(item => item.name).join(', ');


  if (orderItems.length === 0) {
    console.log('Order kosong');
    return;
  }

  if (!selectedPaymentMethod) {
    alert('Silakan pilih metode pembayaran terlebih dahulu!');
    return;
  }

  const customerName = $('#customer_name').val().trim();
  console.log('Nama pelanggan:', customerName); // 🟢 Debug

  if (customerName === '') {
    alert('Silakan isi nama pelanggan terlebih dahulu!');
    return;
  }

  console.log('Lanjut ke konfirmasi'); // 🟢 Debug
  if (confirm(`Proses checkout untuk ${orderNumber}?\nNama Pelanggan: ${customerName}\nMetode Pembayaran: ${selectedPaymentMethod}`)) {
    const total = orderItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    console.log('Mengirim ke PHP...'); // 🟢 Debug

    $.post('daftarproduk.php', {
      process_checkout: true,
      order_data: JSON.stringify(orderItems),
      order_number: orderNumber,
      customer_name: customerName,
      payment_method: selectedPaymentMethod,
      nama_produk: namaProduk,
      total: total
    }, function(response) {
      console.log('Respon PHP:', response); // 🟢 Debug
      try {
        const res = JSON.parse(response);
        if (res.success) {
          alert(res.message);

          // 🔹 Langsung panggil fungsi print setelah checkout sukses
          printReceipt(orderItems, orderNumber, $('#customer_name').val().trim(), selectedPaymentMethod);

          // Reset semua setelah print
          orderItems = [];
          orderNumber = '';
          selectedPaymentMethod = '';
          $('#paymentBtn').html(`<i class="fas fa-credit-card"></i> Metode Pembayaran`);
          $('#customer_name').val('');
          renderOrder();
        } else {
          alert(res.message || 'Terjadi kesalahan saat checkout!');
        }

      } catch (e) {
        console.error('Invalid JSON:', response);
        alert('Terjadi kesalahan saat memproses checkout. Coba lagi.');
      }
    });
  }
});




// Payment Method
$('#paymentBtn').click(function() {
  if (orderItems.length === 0) return;
  
  // Reset selection
  $('.payment-option').removeClass('selected');
  $('#confirmPaymentBtn').prop('disabled', true);
  selectedPaymentMethod = '';
  
  // Show modal
  $('#modalPayment').modal('show');
});

// Select payment method
$(document).on('click', '.payment-option', function() {
  $('.payment-option').removeClass('selected');
  $(this).addClass('selected');
  selectedPaymentMethod = $(this).data('method');
  $('#confirmPaymentBtn').prop('disabled', false);
});

// Confirm payment method
$('#confirmPaymentBtn').click(function() {
  if (selectedPaymentMethod) {
    $('#modalPayment').modal('hide');
    
    // Update payment button text
    $('#paymentBtn').html(`<i class="fas fa-credit-card"></i> ${selectedPaymentMethod}`);
    
    // Show success message
    setTimeout(() => {
      alert(`Metode pembayaran dipilih: ${selectedPaymentMethod}`);
    }, 300);
    
    console.log('Payment Method:', selectedPaymentMethod);
  }
});

function printReceipt(orderItems, orderNumber, customerName, paymentMethod) {
  if (!orderItems.length) return;

  // Pastikan variabel-variabel penting tersedia
  const nama = customerName || 'Umum';
  const metode = paymentMethod || 'Tunai';

  // Buat konten struk
  let receiptContent = `
    <html>
    <head>
      <title>Struk - ${orderNumber}</title>
      <style>
        body { font-family: 'Courier New', monospace; width: 300px; margin: 20px auto; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header h2 { margin: 5px 0; }
        .info { margin-bottom: 10px; font-size: 12px; }
        .items { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; }
        .item { display: flex; justify-content: space-between; margin: 5px 0; font-size: 13px; }
        .item-name { flex: 1; }
        .item-qty { width: 50px; text-align: center; }
        .item-price { width: 80px; text-align: right; }
        .summary { margin-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
        .total { font-weight: bold; font-size: 16px; border-top: 2px solid #000; padding-top: 5px; margin-top: 5px; }
        .footer { text-align: center; margin-top: 15px; font-size: 12px; border-top: 2px dashed #000; padding-top: 10px; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>Indomarket</h2>
        <p>Jl. PNJ No. 20, Depok</p>
        <p>Telp: 021-120987627</p>
      </div>

      <div class="info">
        <div>No Order: ${orderNumber}</div>
        <div>Tanggal: ${new Date().toLocaleString('id-ID')}</div>
        <div>Customer: ${nama}</div>
        <div>Metode Pembayaran: ${metode}</div>
      </div>

      <div class="items">
  `;

  let subtotal = 0;
  let totalDiskon = 0;

  // Loop item pesanan
  orderItems.forEach(item => {
    const hargaAsli = parseFloat(item.original_price);
    const qty = parseInt(item.qty);
    const diskon = parseFloat(item.discount);

    const totalSebelum = hargaAsli * qty;
    const potongan = (diskon / 100) * totalSebelum;
    const totalSesudah = totalSebelum - potongan;

    subtotal += totalSesudah;
    totalDiskon += potongan;

    receiptContent += `
      <div class="item">
        <span class="item-name">${item.name}</span>
        <span class="item-qty">${qty}x</span>
        <span class="item-price">Rp${formatNumber(hargaAsli)}</span>
      </div>
      <div style="font-size:11px;text-align:right;">
        Diskon: ${diskon}% (Potongan: Rp${formatNumber(potongan)})
      </div>
    `;
  });

  const totalAkhir = subtotal;

  receiptContent += `
    </div>
    <div class="summary">
      <div class="summary-row">
        <span>Subtotal (setelah diskon):</span>
        <span>Rp${formatNumber(totalAkhir)}</span>
      </div>
      <div class="summary-row">
        <span>Total Diskon:</span>
        <span>-Rp${formatNumber(totalDiskon)}</span>
      </div>
      <div class="summary-row total">
        <span>TOTAL:</span>
        <span>Rp${formatNumber(totalAkhir)}</span>
      </div>
    </div>
    <div class="footer">
      <p>Terima kasih atas kunjungan Anda!</p>
      <p>Barang yang sudah dibeli tidak dapat ditukar</p>
    </div>
  </body>
  </html>
  `;

  // Buka jendela print
  const printWindow = window.open('', '_blank', 'width=400,height=600');
  printWindow.document.write(receiptContent);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => printWindow.print(), 250);
}


// Toggle order section on mobile
$('#toggleOrder').click(function() {
  if (orderItems.length > 0) {
    $('#orderSection').toggleClass('show');
  }
});

// Category filter
$('.category-btn').click(function() {
  $('.category-btn').removeClass('active');
  $(this).addClass('active');
  
  const category = $(this).data('category');
  
  if (category === 'all') {
    $('.product-card').show();
  } else {
    $('.product-card').each(function() {
      const productCategory = $(this).data('category');
      if (productCategory === category) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  }
});

// Initialize
$(document).ready(function() {
  // Hide order section initially
  $('#orderSection').hide();
});
</script>

</body>
</html>