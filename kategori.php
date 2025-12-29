<?php
include 'config.php';

// === TAMBAH DATA ===
if (isset($_POST['tambah'])) {
  $nama_kategori = $_POST['nama_kategori'];
  $tanggal = $_POST['tanggal'];

  $query = "INSERT INTO kategori (nama_kategori, tanggal) VALUES ('$nama_kategori', '$tanggal')";
  mysqli_query($conn, $query);
  header("Location: kategori.php");
  exit;
}

// === EDIT DATA ===
if (isset($_POST['edit'])) {
  $id = $_POST['id_kategori'];
  $nama_kategori = $_POST['nama_kategori'];
  $tanggal = $_POST['tanggal'];

  $query = "UPDATE kategori SET nama_kategori='$nama_kategori', tanggal='$tanggal' WHERE id_kategori='$id'";
  mysqli_query($conn, $query);
  header("Location: kategori.php");
  exit;
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
  header("Location: kategori.php");
  exit;
}

// === TAMPILKAN DATA ===
$result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Kategori | Kasir System</title>
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
      <h3 class="text-center mb-4">Kelola Kategori Produk</h3>

      <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalTambah">
        <i class="fas fa-plus-circle"></i> Tambah Kategori
      </button>

      <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Nama Kategori</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
            <td><?= htmlspecialchars($row['tanggal']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm btn-edit"
                      data-toggle="modal"
                      data-target="#editModal"
                      data-id="<?= $row['id_kategori']; ?>"
                      data-nama="<?= $row['nama_kategori']; ?>"
                      data-tanggal="<?= $row['tanggal']; ?>">
                <i class="fas fa-edit"></i>
              </button>
              <a href="?hapus=<?= $row['id_kategori']; ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Yakin ingin menghapus data ini?')">
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
          <h5 class="modal-title">Tambah Kategori</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Tanggal</label>
            <input type="datetime-local" name="tanggal" class="form-control" value="<?= date('Y-m-d\TH:i'); ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Kategori</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_kategori" id="edit_id">
          <div class="mb-2">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
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
  // Isi modal edit otomatis
  $('.btn-edit').click(function() {
    const id = $(this).data('id');
    const nama = $(this).data('nama');
    const tanggal = $(this).data('tanggal');

    $('#edit_id').val(id);
    $('#edit_nama').val(nama);

    // format tanggal untuk input datetime-local
    const formatted = new Date(tanggal).toISOString().slice(0, 16);
    $('#edit_tanggal').val(formatted);
  });
</script>

</body>
</html>
