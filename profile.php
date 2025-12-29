<?php
session_start();
include 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// Simpan data profil (per user)
if (isset($_POST['btn_simpan'])) {
  $username = $_SESSION['username'];
  $nama_depan = $_POST['nama_depan'];
  $nama_belakang = $_POST['nama_belakang'];
  $email = $_POST['email'];
  $alamat = $_POST['alamat'];
  $no_handphone = $_POST['no_handphone'];

  // Cek apakah user ini sudah punya profil
  $cek = mysqli_query($conn, "SELECT * FROM profile WHERE username='$username'");
  if (mysqli_num_rows($cek) > 0) {
    // Update data profil user
    $query = "UPDATE profile SET 
              nama_depan='$nama_depan', 
              nama_belakang='$nama_belakang', 
              email='$email', 
              alamat='$alamat', 
              no_handphone='$no_handphone'
              WHERE username='$username'";
  } else {
    // Tambah data baru
    $query = "INSERT INTO profile (username, nama_depan, nama_belakang, email, alamat, no_handphone)
              VALUES ('$username', '$nama_depan', '$nama_belakang', '$email', '$alamat', '$no_handphone')";
  }
  mysqli_query($conn, $query);
  $success = true;
}

// Ambil data profil user yang login
$username = $_SESSION['username'];
$result = mysqli_query($conn, "SELECT * FROM profile WHERE username='$username' LIMIT 1");
$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile | Inventory System</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      background: #f4f6f9;
    }
    .card-profile {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 30px;
      margin-top: 40px;
    }
    .form-label {
      font-weight: 600;
      color: #2c3e50;
    }
    .btn-primary {
      background-color: #2c3e50;
      border: none;
    }
    .btn-secondary {
      background-color: #bdc3c7;
      border: none;
      color: #2c3e50;
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

<!-- Sidebar -->
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
          <li class="nav-item"><a href="profile.php" class="nav-link active"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>

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
          <li class="nav-item"><a href="profile.php" class="nav-link active"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>

        <?php 
        // =============================
        // MENU UNTUK OWNER
        // =============================
        } elseif ($role == 'owner') { 
        ?>
          <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
          <li class="nav-item"><a href="profile.php" class="nav-link active"><i class="nav-icon fas fa-id-card"></i><p>Profile</p></a></li>
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
  <div class="container">
    <?php if (isset($success)) echo "<div class='alert alert-success'>Profil berhasil disimpan!</div>"; ?>

    <div class="card card-profile">
      <h4 class="mb-4 text-center text-secondary"><i class="fas fa-user-circle"></i> Profil Pengguna</h4>
      <form method="POST">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label class="form-label">Nama Depan</label>
            <input type="text" name="nama_depan" class="form-control" value="<?= $profile['nama_depan'] ?? '' ?>" required>
          </div>
          <div class="form-group col-md-6">
            <label class="form-label">Nama Belakang</label>
            <input type="text" name="nama_belakang" class="form-control" value="<?= $profile['nama_belakang'] ?? '' ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= $profile['email'] ?? '' ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" rows="3"><?= $profile['alamat'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">No. Handphone</label>
          <input type="text" name="no_handphone" class="form-control" value="<?= $profile['no_handphone'] ?? '' ?>">
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" name="btn_simpan" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Simpan</button>
          <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="main-footer text-center">
  <strong>&copy; 2025 Inventory System Handphone</strong> — M Eka Miharja
</footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
