<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
header("Location: index");
    exit;
}
include 'config.php';

// Tambah wali kelas
if (isset($_POST['tambah'])) {
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $nama_wali = mysqli_real_escape_string($conn, $_POST['nama_wali']);
    $nip_wali = mysqli_real_escape_string($conn, $_POST['nip_wali']);
    mysqli_query($conn, "INSERT INTO wali_kelas (kelas, nama_wali, nip_wali) 
                         VALUES ('$kelas', '$nama_wali', '$nip_wali')");
    header("Location: wali_kelas.php");
    exit;
}

// Edit wali kelas
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $nama_wali = mysqli_real_escape_string($conn, $_POST['nama_wali']);
    $nip_wali = mysqli_real_escape_string($conn, $_POST['nip_wali']);
    mysqli_query($conn, "UPDATE wali_kelas SET kelas='$kelas', nama_wali='$nama_wali', nip_wali='$nip_wali' WHERE id=$id");
    header("Location: wali_kelas.php");
    exit;
}

// Hapus wali kelas
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM wali_kelas WHERE id=$id");
    header("Location: wali_kelas.php");
    exit;
}

$waliList = mysqli_query($conn, "SELECT * FROM wali_kelas ORDER BY kelas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Kelola Wali Kelas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap -->
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
      font-weight: 600;
      color: #333;
      text-align: center;
    }
    .card-header {
      font-weight: 600;
      font-size: 1.1rem;
    }
    .btn-custom {
      border-radius: 10px;
      font-weight: 500;
    }
    .table thead th {
      background-color: #007bff;
      color: white;
      border: none;
      text-align: center;
    }
    .table tbody td {
      vertical-align: middle;
      text-align: center;
    }
    .table tbody td:first-child {
      font-weight: 600;
    }
    .modal-header {
      background-color: #007bff;
      color: white;
    }
    .modal-title {
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <h2>Manajemen Wali Kelas</h2>

    <!-- Form Tambah -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-primary text-white rounded-top">Tambah Wali Kelas</div>
      <div class="card-body">
        <form method="post" class="row g-3">
          <div class="col-md-3">
            <input type="text" name="kelas" class="form-control" placeholder="Kelas (misal 5A)" required />
          </div>
          <div class="col-md-5">
            <input type="text" name="nama_wali" class="form-control" placeholder="Nama Wali" required />
          </div>
          <div class="col-md-4">
            <input type="text" name="nip_wali" class="form-control" placeholder="NIP Wali (opsional)" />
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" name="tambah" class="btn btn-success btn-custom">Tambah</button>
            <a href="dashboard" class="btn btn-secondary btn-custom">⬅ Kembali</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white rounded-top">Daftar Wali Kelas</div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped table-bordered align-middle mb-0">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Kelas</th>
              <th>Nama Wali</th>
              <th>NIP</th>
              <th width="20%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($waliList)) {
              echo "<tr>";
              echo "<td>{$no}</td>";
              echo "<td>{$row['kelas']}</td>";
              echo "<td>{$row['nama_wali']}</td>";
              echo "<td>{$row['nip_wali']}</td>";
              echo "<td class='text-center'>
                      <button class='btn btn-warning btn-sm btn-custom' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>Edit</button>
                      <a href='wali_kelas.php?hapus={$row['id']}' class='btn btn-danger btn-sm btn-custom' onclick=\"return confirm('Yakin hapus data ini?')\">Hapus</a>
                    </td>";
              echo "</tr>";

              // Modal Edit
              echo "
              <div class='modal fade' id='editModal{$row['id']}' tabindex='-1'>
                <div class='modal-dialog'>
                  <div class='modal-content'>
                    <form method='post'>
                      <div class='modal-header'>
                        <h5 class='modal-title'>Edit Wali Kelas</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>
                        <input type='hidden' name='id' value='{$row['id']}' />
                        <div class='mb-3'>
                          <label class='form-label'>Kelas</label>
                          <input type='text' name='kelas' class='form-control' value='{$row['kelas']}' required />
                        </div>
                        <div class='mb-3'>
                          <label class='form-label'>Nama Wali</label>
                          <input type='text' name='nama_wali' class='form-control' value='{$row['nama_wali']}' required />
                        </div>
                        <div class='mb-3'>
                          <label class='form-label'>NIP Wali</label>
                          <input type='text' name='nip_wali' class='form-control' value='{$row['nip_wali']}' />
                        </div>
                      </div>
                      <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary btn-custom' data-bs-dismiss='modal'>Batal</button>
                        <button type='submit' name='edit' class='btn btn-primary btn-custom'>Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";
              $no++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


