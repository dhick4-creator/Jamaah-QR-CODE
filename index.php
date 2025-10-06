<?php
session_start();

// Cek apakah sedang update (maintenance mode aktif)
if (file_exists(__DIR__ . "/maintenance.flag")) {
    die("<h1>Sedang update, silakan coba beberapa menit lagi...</h1>");
}
include "config.php";

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') {
        header("Location: dashboard");
    } elseif ($_SESSION['role'] === 'siswa') {
        header("Location: dashboard_siswa.php");
    }
    exit;
}

// Ambil data profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_sekolah, logo FROM profil_sekolah LIMIT 1"));
$nama_sekolah = $profil['nama_sekolah'] ?? 'Nama Sekolah';
$logo = $profil['logo'] ?? 'default.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 100%;
      max-width: 450px;
      animation: slideIn 0.6s ease-out;
    }
    .school-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .school-header img {
      max-height: 100px;
      border-radius: 50%;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    .form-floating > label {
      color: #6c757d;
    }
    .form-control {
      border: none;
      border-bottom: 2px solid #dee2e6;
      border-radius: 0;
      padding: 1rem 0.75rem;
      background: transparent;
    }
    .form-control:focus {
      box-shadow: none;
      border-bottom-color: #007bff;
    }
    .btn-login {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
      border-radius: 50px;
      padding: 12px 30px;
      font-weight: 600;
      font-size: 16px;
      color: white;
      transition: all 0.3s ease;
      width: 100%;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
      background: linear-gradient(135deg, #0056b3, #004085);
    }
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
    @media (max-width: 576px) {
      .login-container {
        padding: 30px 20px;
        margin: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="school-header">
      <img src="uploads/<?php echo htmlspecialchars($logo); ?>" alt="Logo Sekolah">
    </div>
    <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
      <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-triangle"></i> Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Silakan login kembali.
      </div>
    <?php endif; ?>
    <form method="post" action="cek" class="mt-4">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
        <label for="username">Username</label>
      </div>
      <div class="form-floating mb-4">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        <label for="password">Password</label>
      </div>
      <button type="submit" class="btn btn-login">Masuk</button>
    </form>






  </div>


</body>
</html>


