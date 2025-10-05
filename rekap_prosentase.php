<?php
// Halaman submenu untuk Rekap dan Prosentase

session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rekap dan Prosentase Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
      max-width: 800px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-dashboard {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      font-weight: 600;
      font-size: 1.2rem;
      color: white;
      transition: all 0.3s ease;
      height: 150px;
      width: 100%;
      text-align: center;
    }
    .btn-dashboard i {
      font-size: 3rem;
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
    .btn-info-custom {
      background: linear-gradient(135deg, #0288d1, #01579b);
      border: none;
    }
    .btn-info-custom:hover {
      background: linear-gradient(135deg, #0277bd, #014f86);
      box-shadow: 0 8px 20px rgba(2, 119, 189, 0.8);
      transform: translateY(-4px);
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <!-- Header -->
    <div class="card mb-5">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-chart-pie text-primary me-2"></i>Rekap dan Prosentase Absensi</h2>
        <p class="text-muted mb-3">Pilih jenis laporan yang ingin ditampilkan</p>
        <a href="dashboard" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Menu Options -->
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <a href="rekap_bulanan" class="btn btn-dashboard btn-primary-custom">
          <i class="fa-solid fa-calendar-days"></i>
          Rekap Bulanan
        </a>
      </div>
      <div class="col">
        <a href="hadir" class="btn btn-dashboard btn-info-custom">
          <i class="fa-solid fa-chart-pie"></i>
          Prosentase Kehadiran
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


