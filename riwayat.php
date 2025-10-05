<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
include "config.php";
date_default_timezone_set("Asia/Jakarta");

// Pastikan ID siswa ada
if (!isset($_GET['id'])) {
    echo "ID siswa tidak ditemukan.";
    exit;
}

$siswa_id = intval($_GET['id']); // pastikan integer

// Ambil nama dan kelas siswa
$qSiswa = mysqli_query($conn, "SELECT nama, kelas FROM siswa WHERE id = $siswa_id");
if (mysqli_num_rows($qSiswa) === 0) {
    echo "Siswa tidak ditemukan.";
    exit;
}
$siswa = mysqli_fetch_assoc($qSiswa);

// Ambil jam masuk dari profil sekolah
$jam_masuk = '07:00:00'; // default
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'jam_masuk'");
if (mysqli_num_rows($check) > 0) {
    $profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jam_masuk FROM profil_sekolah LIMIT 1"));
    if ($profil && $profil['jam_masuk']) {
        $jam_masuk = $profil['jam_masuk'];
    }
}

// Filter bulan & tahun (default bulan ini)
$bulanFilter = isset($_GET['bulan']) ? $_GET['bulan'] : date("m");
$tahunFilter = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");

// Nama bulan
$namaBulan = [
  1=>"Januari", 2=>"Februari", 3=>"Maret", 4=>"April",
  5=>"Mei", 6=>"Juni", 7=>"Juli", 8=>"Agustus",
  9=>"September", 10=>"Oktober", 11=>"November", 12=>"Desember"
];

// Query riwayat absensi bulan & tahun tertentu
$sql = "
    SELECT tanggal, jam, status
    FROM absensi
    WHERE siswa_id = $siswa_id
      AND MONTH(tanggal) = '" . mysqli_real_escape_string($conn, $bulanFilter) . "'
      AND YEAR(tanggal) = '" . mysqli_real_escape_string($conn, $tahunFilter) . "'
    ORDER BY tanggal DESC, jam DESC
";
$data = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Kehadiran - <?= htmlspecialchars($siswa['nama']) ?></title>
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
    .status-h {
      color: #28a745;
      font-weight: bold;
    }
    .status-t {
      color: #ffc107;
      font-weight: bold;
    }
    .status-s {
      color: #6c757d;
      font-weight: bold;
    }
    .status-i {
      color: #17a2b8;
      font-weight: bold;
    }
    .status-a {
      color: #dc3545;
      font-weight: bold;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-history text-primary me-2"></i>Riwayat Kehadiran</h2>
        <a href="jam_absensi" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Rekap Jam Kehadiran
        </a>
      </div>
    </div>

    <!-- Student Info -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="mb-1"><i class="fas fa-user-graduate text-primary me-2"></i><?= htmlspecialchars($siswa['nama']) ?></h4>
            <p class="text-muted mb-0"><i class="fas fa-school me-1"></i>Kelas <?= htmlspecialchars($siswa['kelas']) ?></p>
          </div>
          <div class="col-md-4 text-md-end">
            <span class="badge bg-primary fs-6">Bulan: <?= $namaBulan[$bulanFilter] ?> <?= $tahunFilter ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <input type="hidden" name="id" value="<?= $siswa_id ?>">
          <div class="col-md-3">
            <label for="bulan" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Bulan
            </label>
            <select name="bulan" class="form-select" id="bulan">
              <?php
              foreach ($namaBulan as $num => $nama) {
                $selected = ($bulanFilter == $num) ? "selected" : "";
                echo "<option value='$num' $selected>$nama</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="tahun" class="form-label fw-bold">
              <i class="fas fa-calendar me-1"></i>Tahun
            </label>
            <select name="tahun" class="form-select" id="tahun">
              <?php
              $tahunSekarang = date("Y");
              for ($t = $tahunSekarang; $t >= $tahunSekarang - 5; $t--) {
                $selected = ($tahunFilter == $t) ? "selected" : "";
                echo "<option value='$t' $selected>$t</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-custom w-100">
              <i class="fas fa-search me-2"></i>Tampilkan
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
                <th>Tanggal</th>
                <th>Jam Absensi</th>
                <th>Status Absensi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($data) > 0): ?>
                <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                  <?php
                  $display_status = $row['status'];
                  $status_class = 'status-' . strtolower($row['status']);
                  if ($row['status'] == 'H' && $row['jam'] > $jam_masuk) {
                    $display_status = 'T';
                    $status_class = 'status-t';
                  }
                  ?>
                  <tr>
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td><strong><?= date('d/m/Y', strtotime($row['tanggal'])) ?></strong></td>
                    <td><code><?= htmlspecialchars($row['jam']) ?></code></td>
                    <td><span class="<?= $status_class ?>"><?= htmlspecialchars($display_status) ?></span></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Tidak ada data riwayat</h5>
                    <p class="text-muted">Pada bulan <strong><?= $namaBulan[$bulanFilter] ?> <?= $tahunFilter ?></strong>, tidak ada data absensi untuk siswa ini.</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


