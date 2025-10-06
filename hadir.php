<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil nama sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_sekolah FROM profil_sekolah LIMIT 1"));
$nama_sekolah = $profil['nama_sekolah'] ?? 'Nama Sekolah';

// Ambil daftar kelas
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Ambil daftar siswa
$sqlSiswa = "SELECT id, nama, kelas FROM siswa";
if ($kelas != '') {
    $sqlSiswa .= " WHERE kelas = '$kelas'";
}
$sqlSiswa .= " ORDER BY nama";
$siswaResult = mysqli_query($conn, $sqlSiswa);

// Hitung absensi per siswa
$rekap = [];
$totalGlobal = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
while ($s = mysqli_fetch_assoc($siswaResult)) {
    $id = $s['id'];
    $rekap[$id] = [
        'nama' => $s['nama'],
        'kelas' => $s['kelas'],
        'H' => 0,
        'I' => 0,
        'S' => 0,
        'A' => 0
    ];

    $qAbs = mysqli_query($conn, "SELECT status FROM absensi 
        WHERE siswa_id = '$id' 
        AND MONTH(tanggal) = '$bulan' 
        AND YEAR(tanggal) = '$tahun'");

    while ($row = mysqli_fetch_assoc($qAbs)) {
        $rekap[$id][$row['status']]++;
        $totalGlobal[$row['status']]++;
    }
}

// Hitung total hari aktif (hari dengan absensi)
$qHari = mysqli_query($conn, "SELECT COUNT(DISTINCT tanggal) as jml 
    FROM absensi 
    WHERE MONTH(tanggal) = '$bulan' 
    AND YEAR(tanggal) = '$tahun'");
$jmlHari = mysqli_fetch_assoc($qHari)['jml'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Absensi Bulanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
    .table th {
      background-color: #007bff;
      color: white;
      border: none;
    }
    .table td {
      vertical-align: middle;
    }
    .summary-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .summary-item {
      text-align: center;
      padding: 15px;
    }
    .predikat {
      font-weight: bold;
      padding: 3px 6px;
      border-radius: 4px;
      display: inline-block;
    }
    .sangatbaik { background: #c8e6c9; color: #256029; }
    .baik { background: #bbdefb; color: #0d47a1; }
    .cukup { background: #fff9c4; color: #f57f17; }
    .kurang { background: #ffe0b2; color: #e65100; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-chart-pie text-primary me-2"></i>Rekapitulasi dan Prosentase Absensi Bulanan</h2>
        <p class="text-muted mb-3">Rekapitulasi dan prosentase kehadiran siswa - <?= date('F Y', strtotime("$tahun-$bulan-01")) ?> (<?= $kelas == '' ? 'Semua Kelas' : "Kelas $kelas" ?>)</p>
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

    <!-- Ringkasan -->
    <div class="card summary-card mb-4">
      <div class="card-body">
        <h5 class="card-title text-center mb-3"><i class="fas fa-chart-bar me-2"></i>Ringkasan Total Absensi</h5>
        <div class="row">
          <div class="col-md-3 summary-item">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <h4 class="mb-1"><?= $totalGlobal['H'] ?></h4>
            <small>Total Hadir</small>
          </div>
          <div class="col-md-3 summary-item">
            <i class="fas fa-envelope fa-2x text-info mb-2"></i>
            <h4 class="mb-1"><?= $totalGlobal['I'] ?></h4>
            <small>Total Izin</small>
          </div>
          <div class="col-md-3 summary-item">
            <i class="fas fa-thermometer-half fa-2x text-warning mb-2"></i>
            <h4 class="mb-1"><?= $totalGlobal['S'] ?></h4>
            <small>Total Sakit</small>
          </div>
          <div class="col-md-3 summary-item">
            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
            <h4 class="mb-1"><?= $totalGlobal['A'] ?></h4>
                <small>Total Alpa</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel absensi -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Nama Siswa</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Alpha</th>
                <th class="text-center">Persentase & Predikat</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              foreach ($rekap as $r) {
                  $totalHadir = $r['H'];
                  $izin = $r['I'];
                  $sakit = $r['S'];
                  $alpha = $r['A'];

                  $persen = $jmlHari > 0 ? round(($totalHadir / $jmlHari) * 100, 1) : 0;

                  // Tentukan predikat
                  if ($persen == 100) {
                      $predikat = "<span class='predikat sangatbaik'>100% Sangat Baik</span>";
                  } elseif ($persen >= 90) {
                      $predikat = "<span class='predikat baik'>{$persen}% Baik</span>";
                  } elseif ($persen >= 80) {
                      $predikat = "<span class='predikat cukup'>{$persen}% Cukup</span>";
                  } else {
                      $predikat = "<span class='predikat kurang'>{$persen}% Kurang</span>";
                  }

                  echo "<tr>
                      <td class='text-center fw-bold'>$no</td>
                      <td><strong>{$r['nama']}</strong></td>
                      <td class='text-center'>{$totalHadir}</td>
                      <td class='text-center'>{$izin}</td>
                      <td class='text-center'>{$sakit}</td>
                      <td class='text-center'>{$alpha}</td>
                      <td class='text-center'>$predikat</td>
                  </tr>";
                  $no++;
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


