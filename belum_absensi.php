<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';

if (isset($_GET['action']) && $_GET['action'] == 'mark') {
    $siswa_id = $_GET['siswa_id'];
    $status = $_GET['status']; // S, I, A
    $tanggal = $_GET['tanggal'];
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, status, jenis_sholat) VALUES ('$siswa_id', '$tanggal', '$status', 'Dhuhur')");
    header("Location: belum_absensi.php?tanggal=$tanggal&kelas=$kelas");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Siswa Belum Hadir</title>
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
    .status-btn {
      margin: 2px;
      border-radius: 20px;
      font-size: 0.8rem;
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
        <h2 class="mb-3"><i class="fas fa-users text-primary me-2"></i>Daftar Siswa Belum Hadir</h2>
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
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $filterKelas = $kelas ? "AND s.kelas = '$kelas'" : '';

              $q = mysqli_query($conn, "
                SELECT s.id, s.nis, s.nama, s.kelas
                FROM siswa s
                WHERE s.status='aktif' $filterKelas
                  AND NOT EXISTS (
                    SELECT 1 FROM absensi a
                    WHERE a.siswa_id = s.id
                      AND a.tanggal = '$tanggal'
                  )
                ORDER BY s.nama
              ");

              if (mysqli_num_rows($q) > 0) {
                $no = 1;
                while ($d = mysqli_fetch_assoc($q)) {
                  echo "<tr>
                    <td class='text-center fw-bold'>{$no}</td>
                    <td><code>{$d['nis']}</code></td>
                    <td><strong>{$d['nama']}</strong></td>
                    <td><span class='badge bg-secondary'>{$d['kelas']}</span></td>
                    <td class='text-center'>
                      <a href='?tanggal=$tanggal&kelas=$kelas&action=mark&siswa_id={$d['id']}&status=S' class='btn btn-warning btn-sm status-btn' title='Tandai Sakit'>
                        <i class='fas fa-thermometer-half me-1'></i>Sakit
                      </a>
                      <a href='?tanggal=$tanggal&kelas=$kelas&action=mark&siswa_id={$d['id']}&status=I' class='btn btn-info btn-sm status-btn' title='Tandai Ijin'>
                        <i class='fas fa-envelope me-1'></i>Ijin
                      </a>
                      <a href='?tanggal=$tanggal&kelas=$kelas&action=mark&siswa_id={$d['id']}&status=A' class='btn btn-danger btn-sm status-btn' title='Tandai Alpa'>
                        <i class='fas fa-times-circle me-1'></i>Alpa
                      </a>
                    </td>
                  </tr>";
                  $no++;
                }
              } else {
                echo "<tr>
                  <td colspan='5' class='empty-state'>
                    <i class='fas fa-check-circle text-success'></i>
                    <h5>Semua Siswa Sudah Ada Record Absensi</h5>
                    <p class='mb-0'>Pada tanggal <strong>" . date('d/m/Y', strtotime($tanggal)) . "</strong>, semua siswa sudah tercatat kehadirannya.</p>
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


