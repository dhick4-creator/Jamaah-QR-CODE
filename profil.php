u<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include "config.php";

$msg_profil = "";

// Ambil data profil
$q = $conn->query("SELECT * FROM profil_sekolah LIMIT 1");
$profil = $q->fetch_assoc();

// Proses update profil sekolah
if (isset($_POST['simpan'])) {
    $nama   = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $kepala = $_POST['kepala'];

    // Upload logo jika ada
    $logo = $profil['logo'];
    if (!empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $logo = "logo_" . time() . "." . $ext;
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }
        move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/" . $logo);
    }

    // Upload background kartu (hanya jpg/jpeg)
    $background = $profil['background_kartu'];
    if (!empty($_FILES['background']['name'])) {
        $ext = strtolower(pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION));
        if ($ext === "jpg" || $ext === "jpeg") {
            $background = "background_" . time() . ".jpg";
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            move_uploaded_file($_FILES['background']['tmp_name'], "uploads/" . $background);
        } else {
            $msg_profil = "<span style='color:red;'>Background harus format JPG!</span>";
        }
    }

    $conn->query("UPDATE profil_sekolah SET
        nama_sekolah     = '$nama',
        alamat           = '$alamat',
        kepala_sekolah   = '$kepala',
        logo             = '$logo',
        background_kartu = '$background'
    WHERE id=" . $profil['id']);

    if (!$msg_profil) {
        $msg_profil = "<div class='progress'>
            <div class='progress-bar progress-bar-striped progress-bar-animated bg-success' role='progressbar' style='width: 100%'></div>
        </div>";
    }

    $q = $conn->query("SELECT * FROM profil_sekolah LIMIT 1");
    $profil = $q->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 700px;
        }
        h2, h3 {
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            text-align: center;
        }
        .btn-custom {
            border-radius: 10px;
            font-weight: 500;
        }
        .form-control {
            border-radius: 8px;
            box-shadow: none;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .form-label {
            font-weight: 500;
        }
        img {
            max-width: 150px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px;
            background: #f9f9f9;
            display: block;
            margin: 10px auto;
        }
        .alert {
            border-radius: 10px;
            font-weight: 500;
        }
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, #007bff, transparent);
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <a href="pengaturan" class="btn btn-secondary btn-custom mb-3">← Kembali ke Pengaturan</a>

        <h2>Profil Sekolah</h2>

        <div class="card mb-4">
            <div class="card-header">Informasi Sekolah</div>
            <div class="card-body">
                <?php if ($msg_profil) echo $msg_profil; ?>
                <form method="POST" enctype="multipart/form-data" class="row g-3" id="profilForm">
                    <div class="col-md-6">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($profil['nama_sekolah']) ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Koordinator</label>
                        <input type="text" name="kepala" class="form-control" value="<?= htmlspecialchars($profil['kepala_sekolah']) ?>" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($profil['alamat']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Logo Sekolah</label>
                        <input type="file" name="logo" class="form-control" accept="image/*" />
                        <?php
                        if ($profil['logo']) {
                            $logoPath = "uploads/" . $profil['logo'];
                            if (file_exists($logoPath)) {
                                $version = filemtime($logoPath);
                                echo "<img src='{$logoPath}?v={$version}' alt='Logo Sekolah' />";
                            }
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Background Kartu (JPG)</label>
                        <small class="text-muted d-block mb-2">
                            Gunakan background dengan warna bagian atas gelap, karena teks di atas akan berwarna putih.
                            <br><a href="background_kartu.jpg" target="_blank">Unduh contoh</a>
                        </small>
                        <input type="file" name="background" class="form-control" accept="image/jpeg" />
                        <?php
                        if ($profil['background_kartu']) {
                            $bgPath = "uploads/" . $profil['background_kartu'];
                            if (file_exists($bgPath)) {
                                $version = filemtime($bgPath);
                                echo "<img src='{$bgPath}?v={$version}' alt='Background Kartu' />";
                            }
                        }
                        ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="simpan" class="btn btn-primary btn-custom w-100">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('profilForm').addEventListener('submit', function() {
            // Hide the form and show progress bar
            this.style.display = 'none';
            const progressDiv = document.createElement('div');
            progressDiv.className = 'progress';
            progressDiv.innerHTML = '<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>';
            this.parentNode.insertBefore(progressDiv, this);
        });
    </script>
</body>
</html>


