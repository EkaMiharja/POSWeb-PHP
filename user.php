<?php
session_start();
include 'config.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// --- Tambah Data
if (isset($_POST['btn_simpan'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $query = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
    mysqli_query($conn, $query);
    header("Location: user.php");
    exit;
}

// --- Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: user.php");
    exit;
}

// --- Update Data
if (isset($_POST['btn_update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', password='$password', role='$role' WHERE id=$id");
    header("Location: user.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User | Inventory System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        .user-card {
            position: relative;
            border-radius: 24px;
            border: none;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: #667eea;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .user-card:hover::before {
            transform: scaleX(1);
        }

        .user-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 4px 16px rgba(0,0,0,0.1);
        }

        .user-card-inner {
            padding: 2rem;
            position: relative;
        }

        .user-icon-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .user-icon {
            font-size: 60px;
            background: #667eea;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 4px rgba(113, 147, 202, 0.54));
            transition: all 0.3s ease;
        }

        .user-card:hover .user-icon {
            transform: scale(1.1) rotateY(10deg);
            filter: drop-shadow(0 2px 4px rgba(113, 147, 202, 0.54));
        }

        .user-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .user-username {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .user-role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }

        .user-role i {
            font-size: 12px;
        }

        .role-owner { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .role-admin { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .role-kasir { 
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
        }

        .user-role:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .user-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-action {
            flex: 1;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
            border: 2px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .btn-action:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-action i {
            margin-right: 4px;
        }

        .btn-edit {
            color: #007bff;
            border-color: #007bff;
            background: transparent;
        }

        .btn-edit:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }

        .btn-delete {
            color: #dc3545;
            border-color: #dc3545;
            background: transparent;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
        }

        .card-decoration {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(32, 201, 151, 0.05));
            top: -30px;
            right: -30px;
            pointer-events: none;
        }

        .user-card:hover .card-decoration {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
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
    <div class="content-wrapper p-4">
        <div class="container-fluid">

            <!-- Form tambah user -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0"><i class="fas fa-user-plus"></i> Tambah User</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <input type="text" name="nama" class="form-control" placeholder="Nama" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="role" class="form-control" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Kasir</option>
                                    <option value="owner">Owner</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-2">
                                <button type="submit" name="btn_simpan" class="btn btn-primary btn-block"><i class="fas fa-save"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards User -->
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card user-card">
                        <div class="card-decoration"></div>
                        <div class="user-card-inner text-center">
                            <div class="user-icon-wrapper">
                                <div class="user-icon"><i class="fas fa-user-circle"></i></div>
                            </div>
                            <h5 class="user-name"><?= htmlspecialchars($row['nama']) ?></h5>
                            <p class="user-username">@<?= htmlspecialchars($row['username']) ?></p>
                            <span class="user-role role-<?= $row['role'] ?>">
                                <i class="fas fa-shield-alt"></i>
                                <?= ucfirst($row['role']) ?>
                            </span>
                            <div class="user-actions">
                                <button class="btn btn-action btn-edit" data-toggle="modal" data-target="#editModal<?= $row['id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('Hapus user ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <div class="form-group">
                                            <label>Nama</label>
                                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role" class="form-control" required>
                                                <option value="admin" <?= $row['role']=='admin'?'selected':'' ?>>Admin</option>
                                                <option value="kasir" <?= $row['role']=='kasir'?'selected':'' ?>>Kasir</option>
                                                <option value="owner" <?= $row['role']=='owner'?'selected':'' ?>>Owner</option>
                                            </select>
                                        </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="btn_update" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
        <!-- FOOTER START -->
        <footer class="main-footer text-center">
            <strong>&copy; 2025 Kasir System</strong> — Kelompok RPL 
        </footer>
        <!-- FOOTER END -->
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>