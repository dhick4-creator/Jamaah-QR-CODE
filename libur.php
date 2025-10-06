<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";

// Ambil nama sekolah dari tabel profil_sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_sekolah FROM profil_sekolah LIMIT 1"));
$nama_sekolah = $profil['nama_sekolah'];



// Tambah hari libur
if (isset($_POST['simpan'])) {
  $tgl = $_POST['tanggal'];
  $desc = $_POST['deskripsi'];
  mysqli_query($conn, "INSERT INTO hari_libur (tanggal, deskripsi) VALUES ('$tgl', '$desc')");
  header("Location: libur.php");
}

// Hapus hari libur
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM hari_libur WHERE id=$id");
  header("Location: libur.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pengaturan Hari Libur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
      max-width: 900px;
    }
    h2 {
      margin-top: 20px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 600;
      color: #333;
    }
    .alert {
      font-weight: 600;
      font-size: 1rem;
    }
    .btn-custom {
      border-radius: 10px;
      font-weight: 500;
    }
    form .form-control {
      border-radius: 8px;
      box-shadow: none;
      border: 1px solid #ced4da;
      transition: border-color 0.3s ease;
    }
    form .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
    .table {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      background-color: white;
    }
    .table th {
      background-color: #007bff;
      color: white;
      border: none;
      text-align: center;
      padding: 10px;
    }
    .table td {
      padding: 8px;
      text-align: center;
      vertical-align: middle;
    }
    .table tbody tr:hover {
      background-color: rgba(0, 123, 255, 0.1);
    }
  </style>
</head>
<body>
  <div class="container mt-4">
    <h2>Pengaturan Hari Libur</h2>

    <!-- Kotak informasi hari Minggu -->
    <div class="alert alert-warning p-3 mb-4" role="alert">
      📅 Hari Minggu sudah otomatis LIBUR / tanggal merah.
    </div>

    <a href="dashboard" class="btn btn-secondary btn-custom mb-3">← Kembali</a>

    <form method="post" class="row g-3 mb-4">
      <div class="col-md-3">
        <input type="date" name="tanggal" class="form-control" required />
      </div>
      <div class="col-md-6">
        <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan libur" required />
      </div>
      <div class="col-md-3">
        <button name="simpan" class="btn btn-primary btn-custom w-100">Simpan</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $q = mysqli_query($conn, "SELECT * FROM hari_libur ORDER BY tanggal DESC");
          while ($r = mysqli_fetch_assoc($q)) {
            $tanggal = date("d-m-Y", strtotime($r['tanggal']));
            $deskripsi = $r['deskripsi'];

            // Buat pesan WA
            $pesan = "Menginformasikan kepada Orang Tua/Wali Siswa bahwa *$nama_sekolah* pada hari $tanggal merupakan hari libur $deskripsi.";
            $pesan_encoded = urlencode($pesan);

            echo "<tr>
              <td>{$tanggal}</td>
              <td>{$deskripsi}</td>
              <td>
                <a href='libur.php?hapus={$r['id']}' class='btn btn-danger btn-sm btn-custom' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
              </td>
            </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>


