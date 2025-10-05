<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas = $_GET['kelas'] ?? '';

// Ubah atau tambah data absensi per siswa
if (isset($_POST['ubah'])) {
  $siswa_id = $_POST['siswa_id'];
  $status = $_POST['status'];
  $keterangan = $_POST['keterangan'];

  $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  if (mysqli_num_rows($cek) > 0) {
    mysqli_query($conn, "UPDATE absensi SET status='$status', keterangan='$keterangan' WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  } else {
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, status, keterangan) VALUES ($siswa_id, '$tanggal', '$status', '$keterangan')");
  }
  header("Location: absensi.php?tanggal=$tanggal&kelas=$kelas");
  exit;
}

// Tombol Hadir Semua
if (isset($_POST['hadir_semua'])) {
  $filterKelas = $kelas ? "WHERE kelas='$kelas'" : "";
  $qsiswa = mysqli_query($conn, "SELECT id FROM siswa $filterKelas");
  while ($s = mysqli_fetch_assoc($qsiswa)) {
    $siswa_id = $s['id'];
    $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
      mysqli_query($conn, "UPDATE absensi SET status='H', keterangan='' WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
    } else {
      mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, status, keterangan) VALUES ($siswa_id, '$tanggal', 'H', '')");
    }
  }
  header("Location: absensi.php?tanggal=$tanggal&kelas=$kelas");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Isi Status Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body {
      background: #f4f4f4;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
      max-width: 1200px;
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
    }
    .table thead th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      font-weight: 600;
    }
    .table tbody tr:hover {
      background-color: rgba(102, 126, 234, 0.1);
    }
    .status-select {
      min-width: 120px;
    }
    .action-btn {
      border-radius: 20px;
      font-size: 0.8rem;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-edit text-info me-2"></i>Isi Status Absensi</h2>
        <p class="text-muted mb-3">Kelola status kehadiran siswa</p>
        <a href="dashboard" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label for="tanggal" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Tanggal
            </label>
            <input type="date" name="tanggal" value="<?= $tanggal ?>" class="form-control" id="tanggal">
          </div>
          <div class="col-md-4">
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
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary btn-custom w-100">
              <i class="fas fa-search me-2"></i>Tampilkan Data
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Action -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="post" class="d-flex justify-content-center">
          <button type="submit" name="hadir_semua" class="btn btn-success btn-custom">
            <i class="fas fa-check-circle me-2"></i>Tandai Semua Hadir (H)
          </button>
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
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $filterKelas = $kelas ? "WHERE s.kelas = '$kelas'" : '';
              $q = mysqli_query($conn, "
                SELECT s.id AS siswa_id, s.nis, s.nama, s.kelas,
                       a.id AS absen_id, a.status, a.keterangan
                FROM siswa s
                LEFT JOIN absensi a ON a.siswa_id = s.id AND a.tanggal = '$tanggal'
                $filterKelas
                ORDER BY s.nama
              ");
              $no = 1;
              while ($d = mysqli_fetch_assoc($q)) {
                ?>
                <tr>
                  <form method="post">
                    <input type="hidden" name="siswa_id" value="<?= $d['siswa_id'] ?>">
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td><code><?= $d['nis'] ?></code></td>
                    <td><strong><?= $d['nama'] ?></strong></td>
                    <td><span class="badge bg-secondary"><?= $d['kelas'] ?></span></td>
                    <td>
                      <select name="status" class="form-select status-select">
                        <option value="H" <?= $d['status'] == 'H' ? 'selected' : '' ?>>Hadir</option>
                        <option value="S" <?= $d['status'] == 'S' ? 'selected' : '' ?>>Sakit</option>
                        <option value="I" <?= $d['status'] == 'I' ? 'selected' : '' ?>>Izin</option>
                        <option value="A" <?= $d['status'] == 'A' ? 'selected' : '' ?>>Alpa</option>
                      </select>
                    </td>
                    <td>
                      <input type="text" name="keterangan" class="form-control" value="<?= $d['keterangan'] ?>" placeholder="Opsional">
                    </td>
                    <td class="text-center">
                      <button type="submit" name="ubah" class="btn btn-success action-btn">
                        <i class="fas fa-save me-1"></i><?= $d['absen_id'] ? 'Simpan' : 'Tambah' ?>
                      </button>
                    </td>
                  </form>
                </tr>
                <?php
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


