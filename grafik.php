<?php
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil daftar kelas
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Query absensi
$query = "SELECT a.tanggal, s.kelas, a.status
          FROM absensi a
          JOIN siswa s ON a.siswa_id = s.id
          WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";

if ($kelas != '') {
  $query .= " AND s.kelas = '$kelas'";
}

$result = mysqli_query($conn, $query);

// Hitung per tanggal & total
$rekapGrafik = [];
$total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $tgl = date('d', strtotime($row['tanggal']));
    if (!isset($rekapGrafik[$tgl])) {
        $rekapGrafik[$tgl] = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
    }
    if (isset($rekapGrafik[$tgl][$row['status']])) {
        $rekapGrafik[$tgl][$row['status']]++;
        $total[$row['status']]++;
    }
}

// Siapkan data untuk Chart.js
$tanggalList = [];
$dataH = [];
$dataS = [];
$dataI = [];
$dataA = [];

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
for ($i = 1; $i <= $jumlahHari; $i++) {
    $tglStr = str_pad($i, 2, '0', STR_PAD_LEFT);
    $tanggalList[] = $tglStr;
    $dataH[] = $rekapGrafik[$tglStr]['H'] ?? 0;
    $dataS[] = $rekapGrafik[$tglStr]['S'] ?? 0;
    $dataI[] = $rekapGrafik[$tglStr]['I'] ?? 0;
    $dataA[] = $rekapGrafik[$tglStr]['A'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grafik Absensi Bulanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
      max-width: 1200px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-custom {
      border-radius: 10px;
      font-weight: 500;
    }
    .chart-container {
      position: relative;
      height: 400px;
      width: 100%;
    }
    .total-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .total-item {
      text-align: center;
      padding: 10px;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-chart-line text-primary me-2"></i>Grafik Absensi Bulanan</h2>
        <p class="text-muted mb-3">Visualisasi data kehadiran siswa per bulan</p>
        <a href="dashboard" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="kelas" class="form-label fw-bold">
              <i class="fas fa-school me-1"></i>Kelas
            </label>
            <select name="kelas" class="form-select" id="kelas">
              <option value="">Semua</option>
              <?php mysqli_data_seek($kelasList, 0);
              while ($k = mysqli_fetch_assoc($kelasList)) {
                $sel = ($k['kelas'] == $kelas) ? 'selected' : '';
                echo "<option $sel value='{$k['kelas']}'>{$k['kelas']}</option>";
              } ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="bulan" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Bulan
            </label>
            <select name="bulan" class="form-select" id="bulan">
              <?php for ($b = 1; $b <= 12; $b++) {
                $sel = ($b == $bulan) ? 'selected' : '';
                echo "<option $sel value='$b'>" . date('F', mktime(0, 0, 0, $b, 10)) . "</option>";
              } ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="tahun" class="form-label fw-bold">
              <i class="fas fa-calendar me-1"></i>Tahun
            </label>
            <input type="number" name="tahun" value="<?= $tahun ?>" class="form-control" id="tahun">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-custom w-100">
              <i class="fas fa-search me-2"></i>Tampilkan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Grafik Chart.js -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="chart-container">
          <canvas id="grafikAbsensi"></canvas>
        </div>
      </div>
    </div>

    <!-- Total Absensi -->
    <div class="card total-card">
      <div class="card-body">
        <h5 class="card-title text-center mb-3"><i class="fas fa-chart-bar me-2"></i>Rekap Total Absensi</h5>
        <div class="row">
          <div class="col-md-3 total-item">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <h4 class="mb-1"><?= $total['H'] ?></h4>
            <small>Hadir (H)</small>
          </div>
          <div class="col-md-3 total-item">
            <i class="fas fa-thermometer-half fa-2x text-warning mb-2"></i>
            <h4 class="mb-1"><?= $total['S'] ?></h4>
            <small>Sakit (S)</small>
          </div>
          <div class="col-md-3 total-item">
            <i class="fas fa-envelope fa-2x text-info mb-2"></i>
            <h4 class="mb-1"><?= $total['I'] ?></h4>
            <small>Izin (I)</small>
          </div>
          <div class="col-md-3 total-item">
            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
            <h4 class="mb-1"><?= $total['A'] ?></h4>
            <small>Alpa (A)</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('grafikAbsensi').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($tanggalList) ?>,
            datasets: [
                {
                    label: 'Hadir (H)',
                    data: <?= json_encode($dataH) ?>,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 128, 0, 0.2)',
                    fill: true
                },
                {
                    label: 'Sakit (S)',
                    data: <?= json_encode($dataS) ?>,
                    borderColor: 'orange',
                    backgroundColor: 'rgba(255, 165, 0, 0.2)',
                    fill: true
                },
                {
                    label: 'Izin (I)',
                    data: <?= json_encode($dataI) ?>,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.2)',
                    fill: true
                },
                {
                    label: 'Alpa (A)',
                    data: <?= json_encode($dataA) ?>,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.2)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


