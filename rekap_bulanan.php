<?php
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Ambil siswa hanya yang aktif
$siswaQuery = "SELECT * FROM siswa WHERE status='aktif'";
if ($kelas != '') {
  $siswaQuery .= " AND kelas = '$kelas'";
}
$siswaQuery .= " ORDER BY CAST(kelas AS UNSIGNED) ASC, kelas ASC, nama ASC";
$siswaResult = mysqli_query($conn, $siswaQuery);

// Ambil data absensi hanya siswa aktif
$absensi = [];
$absensiQuery = "SELECT a.*, s.nis, s.nama FROM absensi a 
                 JOIN siswa s ON a.siswa_id = s.id 
                 WHERE MONTH(a.tanggal) = '$bulan' 
                   AND YEAR(a.tanggal) = '$tahun'
                   AND s.status='aktif'";
if ($kelas != '') {
  $absensiQuery .= " AND s.kelas = '$kelas'";
}
$resultAbsensi = mysqli_query($conn, $absensiQuery);

while ($row = mysqli_fetch_assoc($resultAbsensi)) {
  $sid = $row['siswa_id'];
  $tgl = (int)date('j', strtotime($row['tanggal']));
  $absensi[$sid][$tgl] = $row['status'];
}

// Ambil daftar hari libur dari database
$libur = [];
$queryLibur = mysqli_query($conn, "SELECT tanggal FROM hari_libur");
while ($row = mysqli_fetch_assoc($queryLibur)) {
  $libur[] = $row['tanggal'];
}

// Ambil data profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kepala_sekolah, nip_kepala FROM profil_sekolah LIMIT 1"));



// Tanggal terakhir bulan ini
$tanggal_terakhir = date("j F Y", strtotime("$tahun-$bulan-" . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun)));
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
      max-width: 1400px;
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
    .table {
      border-radius: 10px;
      overflow: hidden;
      font-size: 14px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table th {
      background-color: #007bff;
      color: white;
      border: none;
      text-align: center;
      padding: 8px;
      font-weight: 600;
    }
    .table td {
      vertical-align: middle;
      padding: 6px;
      text-align: center;
    }
    .table tbody tr:hover {
      background-color: rgba(0, 123, 255, 0.05);
    }
    .table-responsive {
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .minggu { color: red; }
    .dot-red { color: red; font-weight: bold; }
    .alpa { color: red; font-weight: bold; }
    .signature-table {
      font-size: 14px;
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-calendar-check text-success me-2"></i>Rekap Absensi Bulanan</h2>
        <p class="text-muted mb-3">Rekapitulasi kehadiran siswa per bulan</p>
        <a href="dashboard" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
          <div class="col-md-2">
            <label for="kelas" class="form-label fw-bold">
              <i class="fas fa-school me-1"></i>Kelas
            </label>
            <select name="kelas" class="form-select" id="kelas">
              <option value="">Semua</option>
              <?php while ($k = mysqli_fetch_assoc($kelasList)) {
                $sel = ($k['kelas'] == $kelas) ? 'selected' : '';
                echo "<option value='{$k['kelas']}' $sel>{$k['kelas']}</option>";
              } ?>
            </select>
          </div>
          <div class="col-md-2">
            <label for="bulan" class="form-label fw-bold">
              <i class="fas fa-calendar-alt me-1"></i>Bulan
            </label>
            <select name="bulan" class="form-select" id="bulan">
              <?php for ($b = 1; $b <= 12; $b++) {
                $sel = ($b == $bulan) ? 'selected' : '';
                echo "<option value='$b' $sel>" . date('F', mktime(0, 0, 0, $b, 10)) . "</option>";
              } ?>
            </select>
          </div>
          <div class="col-md-2">
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
          <div class="col-md-2">
            <a href="cetak_absen.php?kelas=<?= $kelas ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" target="_blank" class="btn btn-success btn-custom w-100">
              <i class="fas fa-print me-2"></i>Cetak PDF
            </a>
          </div>
        </form>
      </div>
    </div>

  <div class="table-responsive">
    <table class="table table-bordered table-sm">
      <thead class="table-primary">
        <tr>
          <th rowspan="2">No</th>
          <th rowspan="2">NIS</th>
          <th rowspan="2">Nama</th>
          <th rowspan="2">Kelas</th>
          <th colspan="<?= $jumlahHari ?>">Tanggal</th>
          <th colspan="4">Rekap</th>
        </tr>
        <tr>
          <?php
          for ($i = 1; $i <= $jumlahHari; $i++) {
            $tanggal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $day = date('w', strtotime($tanggal)); // 0 = Minggu
            $class = ($day == 0) ? 'minggu' : '';
            echo "<th class='$class'>$i</th>";
          }
          ?>
                <th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        while ($siswa = mysqli_fetch_assoc($siswaResult)) {
          $sid = $siswa['id'];
          echo "<tr>";
          echo "<td>$no</td>";
          echo "<td>{$siswa['nis']}</td>";
          echo "<td>{$siswa['nama']}</td>";
          echo "<td>{$siswa['kelas']}</td>";

          $countH = $countS = $countI = $countA = 0;

          for ($i = 1; $i <= $jumlahHari; $i++) {
            $val = $absensi[$sid][$i] ?? '';
            $tanggal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $day = date('w', strtotime($tanggal));

            if ($val == '') {
              if ($day == 0 || in_array($tanggal, $libur)) {
                echo "<td><span class='dot-red'>&bull;</span></td>";
              } else {
                echo "<td></td>";
              }
            } else {
              if ($val == 'H') {
                echo "<td>&bull;</td>";
                $countH++;
              } elseif ($val == 'A') {
                echo "<td class='alpa'>A</td>";
                $countA++;
              } elseif ($val == 'S') {
                echo "<td>S</td>";
                $countS++;
              } elseif ($val == 'I') {
                echo "<td>I</td>";
                $countI++;
              } else {
                echo "<td>$val</td>";
              }
            }
          }

          echo "<td>$countH</td><td>$countS</td><td>$countI</td><td>$countA</td>";
          echo "</tr>";
          $no++;
        }
        ?>
      </tbody>
    </table>
  </div>

  <br><br>
  <table class="w-100 text-center" style="font-size:14px;">
    <tr>
      <td style="width:100%; text-align: right;">
        Probolinggo, <?= $tanggal_terakhir ?><br>
        Koordinator<br><br><br><br>
        <u><?= $profil['kepala_sekolah'] ?? '....................................' ?></u>
      </td>
    </tr>
  </table>

</body>
</html>


