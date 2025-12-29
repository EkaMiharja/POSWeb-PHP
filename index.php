<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Ambil user berdasarkan username
  $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
  $data = mysqli_fetch_assoc($query);

  // Cek apakah user ditemukan
  if ($data) {
    // Jika password belum di-hash:
    if ($password == $data['password']) {
      // Simpan ke session
      $_SESSION['username'] = $data['username'];
      $_SESSION['role'] = $data['role'];

      // Arahkan sesuai role
      if ($data['role'] == 'admin') {
        header("Location: user.php");
      } elseif ($data['role'] == 'kasir') {
        header("Location: daftarproduk.php");
      } elseif ($data['role'] == 'owner') {
        header("Location: laporan.php");
      } else {
        header("Location: login.php");
      }
      exit;
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Username tidak ditemukan!";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Store</title>

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

  <style>
    body {
      margin: 0;
      background-color: #f4f6f9;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-container { display: flex; width: 100%; height: 100vh; }

    .login-image {
      flex: 1;
      background: url('assets/img/store.jpg') center/cover no-repeat;
      filter: brightness(85%);
      position: relative;
    }

    .login-image::after {
      content: "";
      position: absolute;
      top: 0;
      right: 0;
      width: 260px;
      height: 100%;
      background: linear-gradient(to left, rgba(255,255,255,0.8), transparent);
      pointer-events: none;
    }

    .login-form {
      flex: 1;
      background: #fff;
      padding: 80px 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-form h2 { font-weight: 700; margin-bottom: 10px; color: #2d3748; }
    .login-form p { color: #666; margin-bottom: 40px; }
    .login-form .input-group { margin-bottom: 20px; }

    .btn-login {
      background-color: #495678;
      color: #fff;
      font-weight: 600;
      border-radius: 10px;
      transition: 0.3s;
    }

    .btn-login:hover { background-color: #3a4763; }

    .fade-out { opacity: 0; transition: opacity 1s ease; }

    @media (max-width: 768px) {
      .login-container { flex-direction: column; }
      .login-image { height: 300px; }
      .login-form { padding: 40px 30px; }
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="login-image"></div>

  <div class="login-form">
    <h2>Welcome</h2>
    <p>Silakan Login untuk melanjutkan</p>

    <?php if (isset($error)) { ?>
      <div class="alert alert-danger" id="alertBox"><?= $error ?></div>
    <?php } ?>

    <form method="POST">
      <div class="input-group mb-3">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <div class="input-group-append">
          <div class="input-group-text"><span class="fas fa-user"></span></div>
        </div>
      </div>

      <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <div class="input-group-append">
          <div class="input-group-text"><span class="fas fa-lock"></span></div>
        </div>
      </div>

      <button type="submit" name="login" class="btn btn-login btn-block">Login</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Auto-hide Alert -->
<script>
  const alertBox = document.getElementById('alertBox');
  if (alertBox) {
    setTimeout(() => {
      alertBox.classList.add('fade-out');
      setTimeout(() => alertBox.remove(), 1000);
    }, 3000);
  }
</script>

</body>
</html>
