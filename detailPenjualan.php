<?php
include 'config.php'; // koneksi database

// Ambil data produk dari tabel produk
$produkResult = mysqli_query($conn, "SELECT nama FROM produk ORDER BY nama ASC");


// === TAMBAH DATA ===
if (isset($_POST['tambah'])) {
  $order_number = $_POST['order_number'];
  $customer_name = $_POST['customer_name'];
  $nama_produk = implode(", ", $_POST['nama_produk']); // ubah array jadi string
  $payment_method = $_POST['payment_method'];
  $total = $_POST['total'];
  $tanggal = $_POST['tanggal'];

  $query = "INSERT INTO transaksi (order_number, customer_name, nama_produk, payment_method, total, tanggal)
            VALUES ('$order_number', '$customer_name', '$nama_produk', '$payment_method', '$total', '$tanggal')";
  mysqli_query($conn, $query);
  header("Location: detailPenjualan.php");
  exit;
}

// === EDIT DATA ===
if (isset($_POST['edit'])) {
  $id = $_POST['id_transaksi'];
  $order_number = $_POST['order_number'];
  $customer_name = $_POST['customer_name'];
  $nama_produk = implode(", ", $_POST['nama_produk']); // ubah array jadi string
  $payment_method = $_POST['payment_method'];
  $total = $_POST['total'];
  $tanggal = $_POST['tanggal'];

  $query = "UPDATE transaksi SET 
              order_number='$order_number', 
              customer_name='$customer_name',
              nama_produk='$nama_produk',
              payment_method='$payment_method', 
              total='$total', 
              tanggal='$tanggal' 
            WHERE id_transaksi='$id'";
  mysqli_query($conn, $query);
  header("Location: detailPenjualan.php");
  exit;
}


// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM transaksi WHERE id_transaksi='$id'");
  header("Location: detailPenjualan.php");
  exit;
}

// === TAMPILKAN DATA ===
$result = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id_transaksi DESC");

// Fungsi format rupiah
function formatRupiah($angka) {
  return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Detail Penjualan | Kasir System</title>
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
                // Pastikan sesi sudah aktif
                session_start();

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
  <div class="content-wrapper p-4">
    <div class="container mt-4">
      <h3 class="mb-4 text-center">Detail Penjualan</h3>

      <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalTambah">
        <i class="fas fa-plus-circle"></i> Tambah Transaksi
      </button>

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
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['order_number']); ?></td>
            <td><?= htmlspecialchars($row['customer_name']); ?></td>
            <td>
              <?= (empty($row['nama_produk']) || is_null($row['nama_produk'])) 
                    ? '<span class="text-danger font-weight-bold">NULL</span>' 
                    : htmlspecialchars($row['nama_produk']); ?>
            </td>
            <td><?= htmlspecialchars($row['payment_method']); ?></td>
            <td><strong><?= formatRupiah($row['total']); ?></strong></td>
            <td><?= htmlspecialchars($row['tanggal']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm btn-edit" 
                      data-toggle="modal" 
                      data-target="#editModal"
                      data-id="<?= $row['id_transaksi']; ?>"
                      data-order="<?= $row['order_number']; ?>"
                      data-name="<?= $row['customer_name']; ?>"
                      data-produk="<?= $row['nama_produk']; ?>"
                      data-method="<?= $row['payment_method']; ?>"
                      data-total="<?= $row['total']; ?>"
                      data-tanggal="<?= $row['tanggal']; ?>">
                <i class="fas fa-edit"></i>
              </button>
              <a href="?hapus=<?= $row['id_transaksi']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Tambah -->
  <div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Tambah Transaksi</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Order Number</label>
            <input type="text" name="order_number" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Customer Name</label>
            <input type="text" name="customer_name" class="form-control" required>
          </div>
          <div class="mb-2">
  <label>Nama Produk</label>
  <select name="nama_produk[]" class="form-control" multiple required>
    <?php
    mysqli_data_seek($produkResult, 0);
    while ($produk = mysqli_fetch_assoc($produkResult)) {
      echo "<option value='{$produk['nama']}'>{$produk['nama']}</option>";
    }
    ?>
  </select>
  <small class="text-muted">Tekan CTRL (atau CMD di Mac) untuk memilih lebih dari satu produk</small>
</div>
          <div class="mb-2">
            <label>Payment Method</label>
            <select name="payment_method" class="form-control" required>
              <option value="Tunai">Tunai</option>
              <option value="Kartu Debit">Kartu Debit</option>
              <option value="Kartu Kredit">Kartu Kredit</option>
              <option value="E-Wallet">E-Wallet</option>
              <option value="Transfer Bank">Transfer Bank</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Total</label>
            <input type="number" step="0.01" name="total" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Tanggal</label>
            <input type="datetime-local" name="tanggal" class="form-control" value="<?= date('Y-m-d\TH:i'); ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-success">Tambah</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Transaksi</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_transaksi" id="edit_id">
          <div class="mb-2">
            <label>Order Number</label>
            <input type="text" name="order_number" id="edit_order" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Customer Name</label>
            <input type="text" name="customer_name" id="edit_name" class="form-control" required>
          </div>
          <div class="mb-2">
  <label>Nama Produk</label>
  <select name="nama_produk[]" class="form-control" multiple required>
    <?php
    mysqli_data_seek($produkResult, 0);
    while ($produk = mysqli_fetch_assoc($produkResult)) {
      echo "<option value='{$produk['nama']}'>{$produk['nama']}</option>";
    }
    ?>
  </select>
  <small class="text-muted">Tekan CTRL (atau CMD di Mac) untuk memilih lebih dari satu produk</small>
</div>
          <div class="mb-2">
            <label>Payment Method</label>
            <select name="payment_method" id="edit_method" class="form-control" required>
              <option value="Tunai">Tunai</option>
              <option value="Kartu Debit">Kartu Debit</option>
              <option value="Kartu Kredit">Kartu Kredit</option>
              <option value="E-Wallet">E-Wallet</option>
              <option value="Transfer Bank">Transfer Bank</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Total</label>
            <input type="number" step="0.01" name="total" id="edit_total" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Tanggal</label>
            <input type="datetime-local" name="tanggal" id="edit_tanggal" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
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
  $('.btn-edit').click(function() {
    const id = $(this).data('id');
    const order = $(this).data('order');
    const name = $(this).data('name');
    const produk = $(this).data('produk').split(", "); // pisahkan jadi array
    const method = $(this).data('method');
    const total = $(this).data('total');
    const tanggal = $(this).data('tanggal');

    $('#edit_id').val(id);
    $('#edit_order').val(order);
    $('#edit_name').val(name);
    $('#edit_produk').val(produk);
    $('#edit_method').val(method);
    $('#edit_total').val(total);

    // set multiple select
    $('#edit_produk').val(produk);

    const formattedDate = new Date(tanggal).toISOString().slice(0, 16);
    $('#edit_tanggal').val(formattedDate);
  });
</script>

</body>
</html>
