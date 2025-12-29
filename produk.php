<?php
session_start();
include 'config.php';

/**
 * Mengembalikan string <option> untuk dropdown kategori.
 * @param mysqli $conn
 * @param string $selected nama kategori yang harus dipilih (opsional)
 * @return string
 */
function getKategoriOptions($conn, $selected = '') {
  $html = "<option value=''>-- Pilih Kategori --</option>";
  $res = mysqli_query($conn, "SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");
  while ($kat = mysqli_fetch_assoc($res)) {
    $is = ($kat['nama_kategori'] === $selected) ? " selected" : "";
    // escape value untuk safety minimal (jika perlu, gunakan mysqli_real_escape_string saat output lebih kompleks)
    $val = htmlspecialchars($kat['nama_kategori'], ENT_QUOTES);
    $html .= "<option value='{$val}'{$is}>{$val}</option>";
  }
  return $html;
}


// Ambil data kategori untuk dropdown
$kategoriResult = mysqli_query($conn, "SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");

// Tambah produk baru
if (isset($_POST['btn_simpan'])) {
  $nama = $_POST['nama'];
  $satuan = $_POST['satuan'];
  $kategori = $_POST['kategori'];
  $stok = $_POST['stok'];
  $harga = $_POST['harga'];
  $keterangan = $_POST['keterangan'];
  $diskon = $_POST['diskon'];

  $gambar = '';
  if (!empty($_FILES['gambar']['name'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $gambar = time() . "_" . basename($_FILES["gambar"]["name"]);
    move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetDir . $gambar);
  }

  $query = "INSERT INTO produk (nama, gambar, satuan, kategori, stok, harga, keterangan, diskon)
            VALUES ('$nama', '$gambar', '$satuan', '$kategori', '$stok', '$harga', '$keterangan', '$diskon')";
  mysqli_query($conn, $query);
  header("Location: produk.php?success=1");
  exit;
}

// Update produk
if (isset($_POST['btn_update'])) {
  $id = $_POST['id'];
  $nama = $_POST['nama'];
  $satuan = $_POST['satuan'];
  $kategori = $_POST['kategori'];
  $stok = $_POST['stok'];
  $harga = $_POST['harga'];
  $keterangan = $_POST['keterangan'];
  $diskon = $_POST['diskon'];

  $gambar = '';
  if (!empty($_FILES['gambar']['name'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $gambar = time() . "_" . basename($_FILES["gambar"]["name"]);
    move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetDir . $gambar);

    $query = "UPDATE produk SET 
                nama='$nama', 
                satuan='$satuan', 
                kategori='$kategori', 
                stok='$stok', 
                harga='$harga', 
                keterangan='$keterangan', 
                diskon='$diskon', 
                gambar='$gambar' 
              WHERE id='$id'";
  } else {
    $query = "UPDATE produk SET 
                nama='$nama', 
                satuan='$satuan', 
                kategori='$kategori', 
                stok='$stok', 
                harga='$harga', 
                keterangan='$keterangan', 
                diskon='$diskon' 
              WHERE id='$id'";
  }

  mysqli_query($conn, $query);
  header("Location: produk.php?updated=1");
  exit;
}

// Hapus produk
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];

  // Ambil nama produk yang dihapus
  $result = mysqli_query($conn, "SELECT nama FROM produk WHERE id='$id'");
  $row = mysqli_fetch_assoc($result);
  $nama_produk = $row['nama'];

  // Hapus produk
  mysqli_query($conn, "DELETE FROM produk WHERE id='$id'");

  // Ubah nama produk jadi NULL di tabel transaksi
  mysqli_query($conn, "UPDATE transaksi SET nama_produk = NULL 
                       WHERE FIND_IN_SET('$nama_produk', REPLACE(nama_produk, ', ', ',')) > 0");

  header("Location: produk.php");
  exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Produk | Inventory System</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    /* Modern Card Design */
    .product-card {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      margin-bottom: 20px;
    }

    .product-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 12px 35px rgba(102, 126, 234, 0.35);
    }

    .product-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
      pointer-events: none;
    }

    .card-image-container {
      position: relative;
      height: 160px;
      overflow: hidden;
      background: #f8f9fa;
    }

    .product-card:hover .card-image-container img {
      transform: scale(1.15) rotate(2deg);
    }

    .card-image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }

    .discount-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #e53e3e;
      color: white;
      padding: 5px 12px;
      font-size: 0.75rem;
      border-radius: 20px;
      font-weight: 700;
      box-shadow: 0 3px 12px rgba(245, 87, 108, 0.4);
      z-index: 10;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .stock-badge {
      position: absolute;
      bottom: 10px;
      left: 10px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      padding: 4px 10px;
      border-radius: 15px;
      font-size: 0.7rem;
      font-weight: 600;
      color: #333;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      z-index: 10;
    }

    .stock-badge i {
      color: #28a745;
      margin-right: 5px;
    }

    .card-content {
      background: white;
      padding: 15px;
      position: relative;
    }

    .product-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 6px;
      height: 42px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      text-align: center;
    }

    .category-tag {
      display: inline-block;
      background: #667eea;
      color: white;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.7rem;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .price-section {
      background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
      border-radius: 10px;
      padding: 10px;
      margin: 10px 0;
      text-align: center;
    }

    .price-original {
      color: #999;
      text-decoration: line-through;
      font-size: 0.75rem;
      display: block;
      margin-bottom: 3px;
    }

    .price-discounted {
      color: #28a745;
      font-size: 1.1rem;
      font-weight: 800;
      display: block;
    }

    .price-normal {
      color: #667eea;
      font-size: 1.1rem;
      font-weight: 800;
      display: block;
    }

    .product-description {
      color: #718096;
      font-size: 0.75rem;
      line-height: 1.4;
      margin: 8px 0;
      height: 32px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      text-align: center;
    }

    .card-actions {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }

    .btn-action {
      flex: 1;
      border: none;
      border-radius: 8px;
      padding: 8px;
      font-weight: 600;
      font-size: 0.8rem;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    .btn-edit {
      background: #667eea;
      color: white;
    }

    .btn-edit:hover {
      background: linear-gradient(135deg, #667eea 0%, #a0aec0 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(65, 61, 60, 0.4);
    }

    .btn-delete {
      background: #667eea;
      color: white;
    }

    .btn-delete:hover {
      background: linear-gradient(135deg, #667eea 0%, #a0aec0 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(65, 61, 60, 0.4);
    }

    .add-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 65px;
      height: 65px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      font-size: 26px;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .add-btn:hover {
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 10px 35px rgba(102, 126, 234, 0.6);
    }

    /* Info pills */
    .info-pills {
      display: flex;
      gap: 8px;
      margin: 10px 0;
      flex-wrap: wrap;
    }

    .info-pill {
      background: #f7fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 5px 10px;
      font-size: 0.75rem;
      color: #4a5568;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .info-pill i {
      color: #667eea;
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

      <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Produk berhasil ditambahkan!</div>
      <?php elseif(isset($_GET['deleted'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">Produk berhasil dihapus!</div>
      <?php elseif(isset($_GET['updated'])): ?>
        <div class="alert alert-info alert-dismissible fade show">Produk berhasil diupdate!</div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-boxes"></i> Daftar Produk</h4>
      </div>

      <!-- Grid Produk -->
      <div class="row">
        <?php
        $result = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
        while ($row = mysqli_fetch_assoc($result)) {
          $imgPath = !empty($row['gambar']) ? "uploads/{$row['gambar']}" : "https://via.placeholder.com/300x220?text=No+Image";
          $hargaAsli = $row['harga'];
          $diskon = $row['diskon'];
          $hargaDiskon = $hargaAsli - ($hargaAsli * $diskon / 100);

          echo "
          <div class='col-lg-3 col-md-4 col-sm-6'>
            <div class='product-card'>
              " . ($diskon > 0 ? "<span class='discount-badge'><i class='fas fa-tag'></i> -{$row['diskon']}%</span>" : "") . "
              
              <div class='card-image-container'>
                <img src='$imgPath' alt='{$row['nama']}'>
                <span class='stock-badge'>
                  <i class='fas fa-box'></i> {$row['stok']} {$row['satuan']}
                </span>
              </div>
              
              <div class='card-content'>
                <span class='category-tag'><i class='fas fa-folder'></i> {$row['kategori']}</span>
                <h5 class='product-title'>{$row['nama']}</h5>
                
                <div class='price-section'>";
          
          if ($diskon > 0) {
            echo "
                  <span class='price-original'>Rp " . number_format($hargaAsli,0,',','.') . "</span>
                  <span class='price-discounted'>Rp " . number_format($hargaDiskon,0,',','.') . "</span>";
          } else {
            echo "
                  <span class='price-normal'>Rp " . number_format($hargaAsli,0,',','.') . "</span>";
          }
          
          echo "
                </div>
                
                <div class='info-pills'>
                  <span class='info-pill'><i class='fas fa-cube'></i> {$row['satuan']}</span>
                  <span class='info-pill'><i class='fas fa-warehouse'></i> Stok: {$row['stok']}</span>
                </div>
                
                <p class='product-description'>{$row['keterangan']}</p>
                
                <div class='card-actions'>
                  <button class='btn-action btn-edit' data-toggle='modal' data-target='#modalEdit{$row['id']}'>
                    <i class='fas fa-edit'></i> Edit
                  </button>
                  <button class='btn-action btn-delete' onclick=\"confirmDelete({$row['id']})\">
                    <i class='fas fa-trash'></i> Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>";

          // Modal Edit
          echo "
          <div class='modal fade' id='modalEdit{$row['id']}' tabindex='-1'>
            <div class='modal-dialog modal-dialog-centered modal-lg'>
              <div class='modal-content border-0 shadow-lg'>
                <form method='POST' enctype='multipart/form-data'>
                  <div class='modal-header bg-gradient-warning text-white'>
                    <h5 class='modal-title'><i class='fas fa-edit mr-2'></i> Edit Produk</h5>
                    <button type='button' class='close text-white' data-dismiss='modal'>&times;</button>
                  </div>

                  <div class='modal-body px-4 py-3'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <div class='row'>
                      <div class='col-md-6'>
                        <div class='form-group'><label>Nama Produk</label><input type='text' name='nama' class='form-control' value='{$row['nama']}' required></div>
                        <div class='form-group'>
                          <label>Kategori</label>
                          <select name='kategori' class='form-control' required>"
  . getKategoriOptions($conn, $row['kategori']) .
"</select>
                        </div>

                        <div class='form-group'>
                          <label>Satuan</label>
                          <select name='satuan' class='form-control' required>
                            <option value='pcs' " . ($row['satuan']=='pcs'?'selected':'') . ">pcs</option>
                            <option value='box' " . ($row['satuan']=='box'?'selected':'') . ">box</option>
                            <option value='paket' " . ($row['satuan']=='paket'?'selected':'') . ">paket</option>
                            <option value='unit' " . ($row['satuan']=='unit'?'selected':'') . ">unit</option>
                            <option value='lusin' " . ($row['satuan']=='lusin'?'selected':'') . ">lusin</option>
                          </select>
                        </div>
                        <div class='form-group'><label>Stok</label><input type='number' name='stok' class='form-control' value='{$row['stok']}' required></div>
                      </div>

                      <div class='col-md-6'>
                        <div class='form-group'><label>Harga</label><input type='number' name='harga' class='form-control' value='{$row['harga']}' required></div>
                        <div class='form-group'><label>Diskon (%)</label><input type='number' name='diskon' class='form-control' value='{$row['diskon']}'></div>
                        <div class='form-group'><label>Keterangan</label><textarea name='keterangan' class='form-control' rows='2'>{$row['keterangan']}</textarea></div>
                        <div class='form-group'><label>Gambar Baru</label><input type='file' name='gambar' class='form-control-file'><small class='text-muted d-block mt-1'>Kosongkan jika tidak ingin mengganti gambar.</small></div>
                      </div>
                    </div>
                  </div>

                  <div class='modal-footer bg-light'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'><i class='fas fa-times mr-1'></i> Batal</button>
                    <button type='submit' name='btn_update' class='btn btn-warning text-white'><i class='fas fa-save mr-1'></i> Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>";
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Tombol Tambah -->
  <button class="add-btn" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i></button>

  <!-- Modal Tambah -->
  <div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg">
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-gradient-success text-white">
            <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Tambah Produk</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          </div>

          <div class="modal-body px-4 py-3">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group"><label>Nama Produk</label><input type="text" name="nama" class="form-control" required></div>
                <div class="mb-2">
                  <label>Kategori</label>
                  <select name="kategori" class="form-control" required>
                     <?= getKategoriOptions($conn); ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Satuan</label>
                  <select name="satuan" class="form-control" required>
                    <option value="" disabled selected>Pilih satuan</option>
                    <option value="pcs">pcs</option>
                    <option value="box">box</option>
                    <option value="paket">paket</option>
                    <option value="unit">unit</option>
                    <option value="lusin">lusin</option>
                  </select>
                </div>
                <div class="form-group"><label>Stok</label><input type="number" name="stok" class="form-control" required></div>
              </div>

              <div class="col-md-6">
                <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" required></div>
                <div class="form-group"><label>Diskon (%)</label><input type="number" name="diskon" class="form-control"></div>
                <div class="form-group"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2"></textarea></div>
                <div class="form-group"><label>Gambar</label><input type="file" name="gambar" class="form-control-file"></div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
            <button type="submit" name="btn_simpan" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan</button>
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
<script>
  setTimeout(() => { $('.alert').alert('close'); }, 2500);
  
  if (window.location.search.includes('success') || window.location.search.includes('deleted') || window.location.search.includes('updated')) {
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
      window.location.href = '?hapus=' + id;
    }
  }
</script>

</body>
</html>