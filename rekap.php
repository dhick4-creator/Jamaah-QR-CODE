<?php
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil daftar kelas dari tabel siswa
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Query utama rekap absensi berdasarkan siswa_id
$query = "SELECT a.tanggal, s.nis, s.nisn, s.nama, s.kelas, a.status, a.keterangan
          FROM absensi a
          JOIN siswa s ON a.siswa_id = s.id
          WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";

if ($kelas != '') {
  $query .= " AND s.kelas = '$kelas'";
}

$query .= " ORDER BY s.kelas, s.nama, a.tanggal";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Waktu Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body {
      background: #f4f4f4;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
      max-width: 1400px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.95);
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
      font-size: 14px;
    }
    .table thead th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      font-weight: 600;
      text-align: center;
      padding: 8px;
    }
    .table tbody td {
      padding: 6px;
      text-align: center;
    }
    .table tbody tr:hover {
      background-color: rgba(102, 126, 234, 0.1);
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-clipboard-list text-success me-2"></i>Rekap Waktu Absensi</h2>
        <p class="text-muted mb-3">Rekapitulasi waktu kehadiran siswa</p>
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
              <?php while ($k = mysqli_fetch_assoc($kelasList)) {
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
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-custom w-100">
              <i class="fas fa-search me-2"></i>Tampilkan
            </button>
          </div>
          <div class="col-md-1">
            <a href="export_rekap.php?kelas=<?= $kelas ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" target="_blank" class="btn btn-success btn-custom w-100">
              <i class="fas fa-file-excel me-2"></i>Excel
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th><i class="fas fa-calendar-day me-1"></i>Tanggal</th>
                <th><i class="fas fa-id-card me-1"></i>NIS</th>
                <th><i class="fas fa-id-card-alt me-1"></i>NISN</th>
                <th><i class="fas fa-user me-1"></i>Nama</th>
                <th><i class="fas fa-school me-1"></i>Kelas</th>
                <th><i class="fas fa-check-circle me-1"></i>Status</th>
                <th><i class="fas fa-comment me-1"></i>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                  <td><?= $row['tanggal'] ?></td>
                  <td><?= $row['nis'] ?></td>
                  <td><?= $row['nisn'] ?></td>
                  <td class="text-start"><?= $row['nama'] ?></td>
                  <td><?= $row['kelas'] ?></td>
                  <td>
                    <?php
                    $status = $row['status'];
                    $badgeClass = 'secondary';
                    if ($status == 'H') $badgeClass = 'success';
                    elseif ($status == 'A') $badgeClass = 'danger';
                    elseif ($status == 'S') $badgeClass = 'warning';
                    elseif ($status == 'I') $badgeClass = 'info';
                    ?>
                    <span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span>
                  </td>
                  <td class="text-start"><?= $row['keterangan'] ?: '-' ?></td>
                </tr>
                <?php } ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                    Tidak ada data absensi bulan ini.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>


