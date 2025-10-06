<?php
include 'session_check.php';

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru')) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin Absensi Sholat Dhuhur QR</title>
  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f4;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }



    header {
      background-color: #4caf50;
      color: white;
      padding: 15px;
      text-align: center;
      font-weight: 700;
      font-size: 1.8rem;
      letter-spacing: 1px;
    }

    h2 {
      margin: 20px 0;
      text-align: center;
      font-weight: 600;
      color: #333;
    }

    .btn-dashboard {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      font-weight: 600;
      font-size: 1.1rem;
      color: white;
      transition: all 0.3s ease;
      height: 120px;
      width: 100%;
      text-align: center;
    }

    .btn-dashboard i {
      font-size: 2.8rem;
    }

    .btn-success-custom {
      background: linear-gradient(135deg, #4caf50, #2e7d32);
      border: none;
    }

    .btn-success-custom:hover {
      background: linear-gradient(135deg, #45a049, #27632a);
      box-shadow: 0 8px 20px rgba(39, 99, 42, 0.8);
      transform: translateY(-4px);
    }

    .btn-danger-custom {
      background: linear-gradient(135deg, #d32f2f, #9a0007);
      border: none;
    }

    .btn-danger-custom:hover {
      background: linear-gradient(135deg, #b71c1c, #7f0000);
      box-shadow: 0 8px 20px rgba(123, 18, 18, 0.8);
      transform: translateY(-4px);
    }

    .btn-primary-custom {
      background: linear-gradient(135deg, #1976d2, #004ba0);
      border: none;
    }

    .btn-primary-custom:hover {
      background: linear-gradient(135deg, #1565c0, #003c8f);
      box-shadow: 0 8px 20px rgba(21, 101, 192, 0.8);
      transform: translateY(-4px);
    }

    .btn-secondary-custom {
      background: linear-gradient(135deg, #6a1b9a, #38006b);
      border: none;
    }

    .btn-secondary-custom:hover {
      background: linear-gradient(135deg, #4a148c, #2a0054);
      box-shadow: 0 8px 20px rgba(74, 20, 140, 0.8);
      transform: translateY(-4px);
    }

    .btn-dark-custom {
      background: linear-gradient(135deg, #212121, #000000);
      border: none;
    }

    .btn-dark-custom:hover {
      background: linear-gradient(135deg, #000000, #000000);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.8);
      transform: translateY(-4px);
    }

    .btn-info-custom {
      background: linear-gradient(135deg, #0288d1, #01579b);
      border: none;
    }

    .btn-info-custom:hover {
      background: linear-gradient(135deg, #0277bd, #014f86);
      box-shadow: 0 8px 20px rgba(2, 119, 189, 0.8);
      transform: translateY(-4px);
    }

    .btn-warning-custom {
      background: linear-gradient(135deg, #f9a825, #c17900);
      border: none;
      color: #212121;
    }

    .btn-warning-custom:hover {
      background: linear-gradient(135deg, #fbc02d, #a56f00);
      box-shadow: 0 8px 20px rgba(251, 192, 45, 0.8);
      transform: translateY(-4px);
      color: #212121;
    }
  </style>
</head>
<body>


  <header>Dashboard Admin Absensi Sholat</header>

  <h2>Menu Utama</h2>

  <div class="container">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      <div class="col">
        <a href="siswa" class="btn btn-dashboard btn-success-custom">
          <i class="fa-solid fa-user-graduate"></i>
          Data Siswa
        </a>
      </div>
      <div class="col">
        <a href="scan" class="btn btn-dashboard btn-success-custom">
          <i class="fa-solid fa-qrcode"></i>
          SCAN QR
        </a>
      </div>
      <div class="col">
        <a href="belum_absensi" class="btn btn-dashboard btn-danger-custom">
          <i class="fa-solid fa-user"></i>
          Siswa Belum Hadir
        </a>
      </div>
      <div class="col">
        <a href="siswa_terlambat" class="btn btn-dashboard btn-primary-custom">
          <i class="fa-solid fa-stopwatch"></i>
          Siswa Terlambat
        </a>
      </div>
      <div class="col">
        <a href="jam_absensi" class="btn btn-dashboard btn-secondary-custom">
          <i class="fa-solid fa-clock"></i>
          Jam Waktu Absensi
        </a>
      </div>
      <div class="col">
        <a href="rekap_prosentase" class="btn btn-dashboard btn-dark-custom">
          <i class="fa-solid fa-chart-pie"></i>
          Rekap dan Prosentase
        </a>
      </div>
      <div class="col">
        <a href="libur" class="btn btn-dashboard btn-danger-custom">
          <i class="fa-solid fa-plane"></i>
          Hari Libur
        </a>
      </div>
      <!-- <div class="col">
        <a href="export" class="btn btn-dashboard btn-success-custom">
          <i class="fa-solid fa-file-excel"></i>
          Export Excel
        </a>
      </div> -->
      <!-- <div class="col">
        <a href="profil" class="btn btn-dashboard btn-warning-custom">
          <i class="fa-solid fa-school"></i>
          Profil Sekolah
        </a>
      </div> -->
      <!-- <div class="col">
        <a href="wali_kelas" class="btn btn-dashboard btn-warning-custom">
          <i class="fa-solid fa-chalkboard-teacher"></i>
          Wali Kelas
        </a>
      </div> -->
      <div class="col">
        <a href="pengaturan" class="btn btn-dashboard btn-info-custom">
          <i class="fa-solid fa-gear"></i>
          Pengaturan
        </a>
      </div>
      <div class="col">
        <a href="logout" class="btn btn-dashboard btn-danger-custom">
          <i class="fa-solid fa-right-from-bracket"></i>
          Logout
        </a>
      </div>
    </div>
  </div>

  <footer>
    <div class="container d-flex justify-content-start">
      <p class="mb-0">Versi Aplikasi: 1.0.0</p>
    </div>
  </footer>

  <script>
    let timeout;
    const timeoutDuration = 30 * 60 * 1000; // 30 minutes in milliseconds

    function resetTimer() {
      clearTimeout(timeout);
      timeout = setTimeout(logout, timeoutDuration);
    }

    function logout() {
      window.location.href = 'logout.php';
    }

    // Reset timer on any activity
    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('click', resetTimer);
    document.addEventListener('scroll', resetTimer);

    // Start the timer
    resetTimer();
  </script>
</body>
</html>


