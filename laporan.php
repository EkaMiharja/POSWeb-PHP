<?php
include 'config.php';

// Inisialisasi variabel
$periode_awal = '';
$periode_akhir = '';
$total_transaksi = 0;
$total_penjualan = 0;
$total_item = 0;
$result = null;

// Jika form filter dikirim
if (isset($_POST['tampilkan'])) {
  $periode_awal = $_POST['periode_awal'];
  $periode_akhir = $_POST['periode_akhir'];

  // Ambil data sesuai rentang tanggal
  $query = "SELECT * FROM transaksi WHERE DATE(tanggal) BETWEEN '$periode_awal' AND '$periode_akhir' ORDER BY tanggal DESC";
  $result = mysqli_query($conn, $query);

  $order_numbers = [];
  $total_penjualan = 0;
  $total_item = 0;

  while ($row = mysqli_fetch_assoc($result)) {
    $order_numbers[$row['order_number']] = true;
    $total_penjualan += $row['total'];

    $produkList = explode(',', $row['nama_produk']);
    $total_item += count($produkList);
  }

  // Reset pointer hasil agar bisa dibaca ulang untuk tabel detail
  mysqli_data_seek($result, 0);

  $total_transaksi = count($order_numbers);
}

// Format rupiah
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User | Inventory System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
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
            session_start();
            $role = $_SESSION['role'] ?? '';

            if ($role == 'admin') { ?>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-database"></i>
                        <p>Kelola<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="user.php" class="nav-link"><i class="fas fa-users nav-icon"></i><p>User</p></a></li>
                        <li class="nav-item"><a href="produk.php" class="nav-link"><i class="fas fa-mobile-alt nav-icon"></i><p>Produk</p></a></li>
                        <li class="nav-item"><a href="kategori.php" class="nav-link"><i class="fas fa-layer-group nav-icon"></i><p>Kategori</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>
            <?php } elseif ($role == 'kasir') { ?>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>Transaksi<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="daftarproduk.php" class="nav-link"><i class="nav-icon fas fa-cash-register"></i><p>Penjualan</p></a></li>
                        <li class="nav-item"><a href="detailPenjualan.php" class="nav-link"><i class="nav-icon fas fa-receipt"></i><p>Detail Penjualan</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>
            <?php } elseif ($role == 'owner') { ?>
                <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>
            <?php } ?>
                <li class="nav-item">
                    <a href="index.php" class="nav-link text-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a>
                </li>
            </ul>
        </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper p-4">
    <div class="container mt-4">
      <h3 class="mb-4 text-center">Laporan Penjualan</h3>

      <!-- Filter -->
      <form method="POST" class="no-print mb-4">
        <div class="row">
          <div class="col-md-4">
            <label>Periode Awal</label>
            <input type="date" name="periode_awal" class="form-control" value="<?= $periode_awal ?>" required>
          </div>
          <div class="col-md-4">
            <label>Periode Akhir</label>
            <input type="date" name="periode_akhir" class="form-control" value="<?= $periode_akhir ?>" required>
          </div>
          <div class="col-md-4 align-self-end">
            <button type="submit" name="tampilkan" class="btn btn-primary btn-block">
              <i class="fas fa-search"></i> Tampilkan
            </button>
          </div>
        </div>
      </form>

      <?php if ($periode_awal && $periode_akhir): ?>
      <div class="card">
        <div class="card-body">
          <h5 class="text-center mb-4">Periode: <?= htmlspecialchars($periode_awal) ?> s/d <?= htmlspecialchars($periode_akhir) ?></h5>

          <!-- Tabel ringkasan -->
          <table class="table table-bordered text-center">
            <thead class="thead-dark">
              <tr>
                <th>Periode Awal</th>
                <th>Periode Akhir</th>
                <th>Total Transaksi</th>
                <th>Total Penjualan</th>
                <th>Total Item Terjual</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= htmlspecialchars($periode_awal) ?></td>
                <td><?= htmlspecialchars($periode_akhir) ?></td>
                <td><?= $total_transaksi ?></td>
                <td><strong><?= formatRupiah($total_penjualan) ?></strong></td>
                <td><?= $total_item ?></td>
              </tr>
            </tbody>
          </table>

          <!-- === Tambahan: Tabel Detail Penjualan === -->
          <h5 class="mt-5 mb-3 text-center">Detail Transaksi</h5>
          <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
              <tr>
                <th>No</th>
                <th>Order Number</th>
                <th>Customer Name</th>
                <th>Nama Produk</th>
                <th>Payment Method</th>
                <th>Total</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['order_number']); ?></td>
                    <td><?= htmlspecialchars($row['customer_name']); ?></td>
                    <td><?= (empty($row['nama_produk']) || is_null($row['nama_produk'])) ? '<span class="text-muted">NULL</span>' : htmlspecialchars($row['nama_produk']); ?></td>
                    <td><?= htmlspecialchars($row['payment_method']); ?></td>
                    <td><strong><?= formatRupiah($row['total']); ?></strong></td>
                    <td><?= htmlspecialchars($row['tanggal']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-muted">Tidak ada data transaksi pada periode ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Bagian Cetak -->
          <div class="mt-4">
            <form id="printForm" class="no-print">
              <div class="form-row">
                <div class="col-md-5">
                  <label>Diisi Oleh</label>
                  <input type="text" id="diisi_oleh" class="form-control" placeholder="Nama petugas..." required>
                </div>
                <div class="col-md-5">
                  <label>Tanggal Dibuat</label>
                  <input type="date" id="tanggal_dibuat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2 align-self-end">
                  <button type="button" onclick="printLaporan()" class="btn btn-success btn-block">
                    <i class="fas fa-print"></i> Print
                  </button>
                </div>
              </div>
            </form>

            <div id="printFooter" class="mt-5 text-right" style="display:none;">
              <p><strong>Diisi Oleh:</strong> <span id="printNama"></span></p>
              <p><strong>Tanggal Dibuat:</strong> <span id="printTanggal"></span></p>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <footer class="main-footer text-center">
    <strong>&copy; 2025 Kasir System</strong> — Kelompok RPL
  </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function printLaporan() {
  const nama = document.getElementById('diisi_oleh').value.trim();
  const tanggal = document.getElementById('tanggal_dibuat').value;

  if (!nama || !tanggal) {
    alert('Mohon isi nama petugas dan tanggal dibuat sebelum mencetak!');
    return;
  }

  document.getElementById('printNama').textContent = nama;
  document.getElementById('printTanggal').textContent = tanggal;
  document.getElementById('printFooter').style.display = 'block';

  window.print();

  setTimeout(() => {
    document.getElementById('printFooter').style.display = 'none';
  }, 500);
}
</script>
</body>
</html>
