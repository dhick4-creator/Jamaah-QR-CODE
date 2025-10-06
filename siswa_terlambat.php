<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$kelas   = $_GET['kelas'] ?? '';
$tahun   = $_GET['tahun'] ?? date('Y'); // default tahun berjalan

// Simpan jam_telat di session jika diset
if (isset($_GET['jam_telat'])) {
    $_SESSION['jam_telat'] = $_GET['jam_telat'];
}
$jamTelat = $_SESSION['jam_telat'] ?? '12:30'; // default jam terlambat
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Siswa Terlambat</title>
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
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .table {
      border-radius: 10px;
      overflow: hidden;
    }
    .table th {
      background-color: #007bff;
      color: white;
      border: none;
    }
    .table td {
      vertical-align: middle;
    }
    .table tbody tr:hover {
      background-color: rgba(102, 126, 234, 0.1);
    }
    .empty-state {
      text-align: center;
      padding: 50px;
      color: #6c757d;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      opacity: 0.5;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-clock text-warning me-2"></i>Daftar Siswa Terlambat</h2>
        <p class="text-muted mb-3">Tahun <?= $tahun ?> (> <?= htmlspecialchars($jamTelat) ?>)</p>
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
              <option value="">Semua Kelas</option>
              <?php
              $qkelas = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
              while ($k = mysqli_fetch_assoc($qkelas)) {
                $selected = $k['kelas'] == $kelas ? 'selected' : '';
                echo "<option $selected>{$k['kelas']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="tahun" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Tahun
            </label>
            <select name="tahun" class="form-select" id="tahun">
              <?php
              $qtahun = mysqli_query($conn, "SELECT DISTINCT YEAR(tanggal) as th FROM absensi ORDER BY th DESC");
              while ($t = mysqli_fetch_assoc($qtahun)) {
                $selected = $t['th'] == $tahun ? 'selected' : '';
                echo "<option value='{$t['th']}' $selected>{$t['th']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="jam_telat" class="form-label fw-bold">
              <i class="fas fa-clock me-1"></i>Jam Terlambat
            </label>
            <input type="time" name="jam_telat" value="<?= htmlspecialchars($jamTelat) ?>" class="form-control" id="jam_telat">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-custom w-100">
              <i class="fas fa-search me-2"></i>Tampilkan Data
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Tanggal</th>
                <th>Jam</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $filterKelas = $kelas ? "AND s.kelas = '$kelas'" : '';

              $q = mysqli_query($conn, "
                SELECT s.id, s.nama, s.kelas, a.tanggal, a.jam
                FROM absensi a
                JOIN siswa s ON a.siswa_id = s.id
                WHERE YEAR(a.tanggal) = '$tahun'
                  AND a.jam > '$jamTelat:00'
                  AND s.status = 'aktif' $filterKelas
                ORDER BY s.nama, a.tanggal
              ");

              if (mysqli_num_rows($q) > 0) {
                $no = 1;
                while ($d = mysqli_fetch_assoc($q)) {
                  echo "<tr>
                    <td class='text-center fw-bold'>{$no}</td>
                    <td><strong>{$d['nama']}</strong></td>
                    <td><span class='badge bg-secondary'>{$d['kelas']}</span></td>
                    <td>" . date('d/m/Y', strtotime($d['tanggal'])) . "</td>
                    <td><code>{$d['jam']}</code></td>
                  </tr>";
                  $no++;
                }
              } else {
                echo "<tr>
                  <td colspan='5' class='empty-state'>
                    <i class='fas fa-check-circle text-success'></i>
                    <h5>Tidak Ada Siswa Terlambat</h5>
                    <p class='mb-0'>Pada tahun <strong>$tahun</strong> dengan jam terlambat > <strong>$jamTelat</strong>, tidak ada siswa yang tercatat terlambat.</p>
                  </td>
                </tr>";
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


