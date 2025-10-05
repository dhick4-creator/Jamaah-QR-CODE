<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

// Ambil jam masuk dari profil sekolah
$jam_masuk = '07:00:00'; // default
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'jam_masuk'");
if (mysqli_num_rows($check) > 0) {
    $profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jam_masuk FROM profil_sekolah LIMIT 1"));
    if ($profil && $profil['jam_masuk']) {
        $jam_masuk = $profil['jam_masuk'];
    }
}

// Ambil parameter filter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date("Y-m-d");
$kelasFilter = isset($_GET['kelas']) ? $_GET['kelas'] : "";
$namaFilter = isset($_GET['nama']) ? $_GET['nama'] : "";

// Ambil daftar kelas unik
$kelasList = [];
$qKelas = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
while ($row = mysqli_fetch_assoc($qKelas)) {
    $kelasList[] = $row['kelas'];
}

// Query rekap
$sql = "
    SELECT a.id, s.id AS siswa_id, s.nama, s.kelas, a.jam, a.status
    FROM absensi a
    JOIN siswa s ON a.siswa_id = s.id
    WHERE a.tanggal = '" . mysqli_real_escape_string($conn, $tanggal) . "'
";
if ($kelasFilter !== "") {
    $sql .= " AND s.kelas = '" . mysqli_real_escape_string($conn, $kelasFilter) . "'";
}
if ($namaFilter !== "") {
    $sql .= " AND s.nama LIKE '%" . mysqli_real_escape_string($conn, $namaFilter) . "%'";
}
$sql .= " ORDER BY s.kelas, s.nama";

$data = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Jam Kehadiran</title>
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
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-clock text-primary me-2"></i>Rekap Jam Kehadiran</h2>
        <a href="dashboard" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="tanggal" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Tanggal
            </label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control" id="tanggal">
          </div>
          <div class="col-md-3">
            <label for="kelas" class="form-label fw-bold">
              <i class="fas fa-school me-1"></i>Kelas
            </label>
            <select name="kelas" class="form-select" id="kelas">
              <option value="">Semua Kelas</option>
              <?php foreach ($kelasList as $kelas): ?>
                <option value="<?= htmlspecialchars($kelas) ?>" <?= ($kelas == $kelasFilter) ? "selected" : "" ?>>
                  <?= htmlspecialchars($kelas) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="nama" class="form-label fw-bold">
              <i class="fas fa-user me-1"></i>Nama Siswa
            </label>
            <input type="text" name="nama" value="<?= htmlspecialchars($namaFilter) ?>" class="form-control" id="nama" placeholder="Cari nama...">
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
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jam Absensi</th>
                <th>Status Absensi</th>
                <th class="text-center">Riwayat</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($data) > 0): ?>
                <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                  <tr>
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kelas']) ?></span></td>
                    <td><code><?= htmlspecialchars($row['jam']) ?></code></td>
                    <td>
                      <?php
                      $display_status = $row['status'];
                      if ($row['status'] == 'H' && $row['jam'] > $jam_masuk) {
                        $display_status = 'T';
                      }
                      echo htmlspecialchars($display_status);
                      ?>
                    </td>
                    <td class="text-center">
                      <a href="riwayat.php?id=<?= $row['siswa_id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-history me-1"></i>Lihat Riwayat
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Tidak ada data absensi</h5>
                    <p class="text-muted">Pada tanggal <strong><?= date('d/m/Y', strtotime($tanggal)) ?></strong>, tidak ada siswa yang tercatat hadir.</p>
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


