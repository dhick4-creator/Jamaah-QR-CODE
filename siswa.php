<?php
// Aktifkan error reporting untuk debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';



if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    die('Error: vendor/autoload.php not found. Please run composer install.');
}

/* ==== Buat tabel users jika belum ada ====
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  password VARCHAR(100) NOT NULL,
  role ENUM('admin','siswa') DEFAULT 'siswa'
);
============================================================================= */

// Proses simpan (tambah baru)
if (isset($_POST['simpan'])) {
    $nis   = $_POST['nis'];
    $nisn  = $_POST['nisn'];
    $nama  = $_POST['nama'];
    $kelas = $_POST['kelas'];

    if (!mysqli_query($conn, "INSERT INTO siswa (nis, nisn, nama, kelas, status)
                         VALUES ('$nis', '$nisn', '$nama', '$kelas', 'aktif')")) {
        echo "Error menyimpan data siswa: " . mysqli_error($conn);
        exit;
    }

    // Buat akun user untuk siswa
    $username = $nisn;
    $password = md5($nisn);
    $role     = 'siswa';

    $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' LIMIT 1");
    if (mysqli_num_rows($cek_user) == 0) {
        if (!mysqli_query($conn, "INSERT INTO users (username, nama, password, role)
                             VALUES ('$username', '$nama', '$password', '$role')")) {
            echo "Error membuat akun siswa: " . mysqli_error($conn);
            exit;
        }
    }

    // Generate QR Jamaah
    $qr_dir = "assets/qr/";
    if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
    if (extension_loaded('gd')) {
        @QRcode::png($nisn, $qr_dir . "$nisn.png", QR_ECLEVEL_L, 4);
    }

    echo "<script>alert('Data siswa berhasil disimpan.');window.location='siswa.php';</script>";
    exit;
}

// Proses update data (edit)
if (isset($_POST['update'])) {
    $id    = intval($_POST['id']);
    $nis   = $_POST['nis'];
    $nisn  = $_POST['nisn'];
    $nama  = $_POST['nama'];
    $kelas = $_POST['kelas'];

    $res_old = mysqli_query($conn, "SELECT nisn FROM siswa WHERE id=$id LIMIT 1");
    $old     = mysqli_fetch_assoc($res_old);
    $old_nisn = $old['nisn'];

    if (!mysqli_query($conn, "UPDATE siswa
                         SET nis='$nis', nisn='$nisn', nama='$nama', kelas='$kelas'
                         WHERE id=$id")) {
        echo "Error memperbarui data siswa: " . mysqli_error($conn);
        exit;
    }

    if (!mysqli_query($conn, "UPDATE users
                         SET username='$nisn', nama='$nama', password=md5('$nisn')
                         WHERE username='$old_nisn' AND role='siswa'")) {
        echo "Error memperbarui akun siswa: " . mysqli_error($conn);
        exit;
    }

    $qr_dir = "assets/qr/";
    if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
    if (extension_loaded('gd')) {
        @QRcode::png($nisn, $qr_dir . "$nisn.png", QR_ECLEVEL_L, 4);
    }

    echo "<script>alert('Data siswa berhasil diperbarui.');window.location='siswa.php';</script>";
    exit;
}

// Tandai siswa keluar
if (isset($_GET['keluar'])) {
    $id = intval($_GET['keluar']);
    $res = mysqli_query($conn, "SELECT nisn FROM siswa WHERE id=$id LIMIT 1");
    $data = mysqli_fetch_assoc($res);
    $nisn_keluar = $data['nisn'];

    mysqli_query($conn, "UPDATE siswa SET status='keluar' WHERE id=$id");
    mysqli_query($conn, "DELETE FROM users WHERE username='$nisn_keluar' AND role='siswa'");

    header("Location: siswa.php");
    exit;
}

// Generate akun massal
if (isset($_POST['generate_akun'])) {
    $q_siswa = mysqli_query($conn, "SELECT nisn, nama FROM siswa WHERE status='aktif'");
    $count = 0;
    while ($s = mysqli_fetch_assoc($q_siswa)) {
        $username = $s['nisn'];
        $nama     = $s['nama'];
        $password = md5($s['nisn']);
        $role     = 'siswa';

        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' LIMIT 1");
        if (mysqli_num_rows($cek) == 0) {
            if (!mysqli_query($conn, "INSERT INTO users (username, nama, password, role)
                                 VALUES ('$username', '$nama', '$password', '$role')")) {
                echo "Error membuat akun untuk $nama: " . mysqli_error($conn);
                exit;
            }
            // Generate QR Jamaah
            $qr_dir = "assets/qr/";
            if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
            if (extension_loaded('gd')) {
                @QRcode::png($username, $qr_dir . "$username.png", QR_ECLEVEL_L, 4);
            }
            $count++;
        }
    }
    echo "<script>alert('Generate akun selesai. $count akun baru dibuat.');window.location='siswa.php';</script>";
    exit;
}

// Ambil data untuk edit jika ada
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM siswa WHERE id=$id LIMIT 1");
    $edit_data = mysqli_fetch_assoc($res);
}

// === Tambahan: Filter Kelas ===
$list_kelas   = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa WHERE status='aktif' ORDER BY kelas ASC");
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .qr-img {
      border-radius: 5px;
      border: 1px solid #ddd;
      transition: transform 0.2s;
    }
    .qr-img:hover {
      transform: scale(1.1);
    }
    .sort-icon {
      font-size: 0.8em;
      margin-left: 5px;
    }
  </style>
</head>
<body class="bg-light">

  <div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h2 class="mb-3"><i class="fas fa-users text-primary me-2"></i>Data Siswa</h2>
        <a href="dashboard" class="btn btn-outline-secondary btn-custom">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
      </div>
    </div>

    <!-- Form Input / Edit -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">
          <i class="fas fa-<?php echo $edit_data ? 'edit' : 'plus-circle'; ?> text-primary me-2"></i>
          <?php echo $edit_data ? 'Edit Siswa' : 'Tambah Siswa Baru'; ?>
        </h5>
        <form method="post" class="row g-3">
          <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
          <div class="col-md-2">
            <input type="number" name="nis" class="form-control" placeholder="NIS" required value="<?= $edit_data['nis'] ?? '' ?>">
          </div>
          <div class="col-md-2">
            <input type="number" name="nisn" class="form-control" placeholder="NISN" required value="<?= $edit_data['nisn'] ?? '' ?>">
          </div>
          <div class="col-md-3">
            <input type="text" name="nama" class="form-control" placeholder="Nama" required value="<?= $edit_data['nama'] ?? '' ?>">
          </div>
          <div class="col-md-2">
            <input type="text" name="kelas" class="form-control" placeholder="Kelas" required value="<?= $edit_data['kelas'] ?? '' ?>">
          </div>
          <div class="col-md-3">
            <?php if ($edit_data): ?>
              <button type="submit" name="update" class="btn btn-warning btn-custom w-100">Update</button>
              <a href="siswa" class="btn btn-secondary btn-custom w-100 mt-2">Batal</a>
            <?php else: ?>
              <button type="submit" name="simpan" class="btn btn-primary btn-custom w-100">Simpan</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">
          <i class="fas fa-tools text-primary me-2"></i>Aksi Cepat
        </h5>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <a href="cetak_kartu" class="btn btn-success btn-custom w-100" target="_blank">
              <i class="fas fa-print me-2"></i>Cetak Kartu QR
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="foto_siswa" class="btn btn-warning btn-custom w-100">
              <i class="fas fa-camera me-2"></i>Foto Siswa
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="siswa_keluar" class="btn btn-outline-danger btn-custom w-100">
              <i class="fas fa-user-times me-2"></i>Siswa Keluar
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="import_siswa" class="btn btn-info btn-custom w-100">
              <i class="fas fa-file-excel me-2"></i>Import Excel
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Kelas -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3">
          <div class="col-md-4">
            <label for="kelas" class="form-label">Filter Kelas</label>
            <select name="kelas" id="kelas" class="form-select" onchange="this.form.submit()">
              <option value="">-- Semua Kelas --</option>
              <?php while ($k = mysqli_fetch_assoc($list_kelas)): ?>
                <option value="<?= $k['kelas'] ?>" <?= ($filter_kelas == $k['kelas']) ? 'selected' : '' ?>>
                  <?= $k['kelas'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Data Siswa -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>No</th>
                <?php
                $columns = ['nis', 'nisn', 'nama', 'kelas'];
                foreach ($columns as $col) {
                  $current_order = (isset($_GET['sort']) && $_GET['sort'] == $col && isset($_GET['order'])) ? $_GET['order'] : 'asc';
                  $next_order = ($current_order == 'asc') ? 'desc' : 'asc';
                  $icon = '';
                  if (isset($_GET['sort']) && $_GET['sort'] == $col) {
                    $icon = $current_order == 'asc' ? '▲' : '▼';
                  }
                  echo "<th><a href='?sort=$col&order=$next_order&kelas=$filter_kelas' class='text-white text-decoration-none'>" . strtoupper($col) . " $icon</a></th>";
                }
                ?>
                <th class="text-center">QR Jamaah</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sort = isset($_GET['sort']) ? $_GET['sort'] : 'nama';
              $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
              $allowed_sorts = ['nis', 'nisn', 'nama', 'kelas'];
              if (!in_array($sort, $allowed_sorts)) $sort = 'nama';
              if (!in_array($order, ['asc', 'desc'])) $order = 'asc';

              $sql = "SELECT * FROM siswa WHERE status='aktif'";
              if ($filter_kelas != '') {
                  $kelas_safe = mysqli_real_escape_string($conn, $filter_kelas);
                  $sql .= " AND kelas='$kelas_safe'";
              }
              $sql .= " ORDER BY $sort $order";

              $q = mysqli_query($conn, $sql);
              $no = 1;
              while ($row = mysqli_fetch_assoc($q)) {
                echo "<tr>
                  <td>{$no}</td>
                  <td>{$row['nis']}</td>
                  <td>{$row['nisn']}</td>
                  <td>{$row['nama']}</td>
                  <td>{$row['kelas']}</td>
                  <td class='text-center'>
                    <a href='assets/qr/{$row['nisn']}.png' target='_blank'>
                      <img src='assets/qr/{$row['nisn']}.png' width='50' class='qr-img'>
                    </a>
                  </td>
                  <td class='text-center'>
                    <a href='siswa.php?edit={$row['id']}' class='btn btn-outline-info btn-sm me-1'>Edit</a>
                    <a href='siswa.php?keluar={$row['id']}' class='btn btn-outline-warning btn-sm' onclick='return confirm(\"Yakin siswa ini keluar?\")'>Keluar</a>
                  </td>
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

</body>
</html>


