<?php
include 'session_check.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include "config.php";

$msg_password = "";

// Proses ubah password admin (pakai MD5)
if (isset($_POST['ubah_password'])) {
    $old     = $_POST['old_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Ambil data admin
    $qAdmin = $conn->query("SELECT * FROM users WHERE username='admin' LIMIT 1");
    $admin  = $qAdmin->fetch_assoc();

    if (!$admin) {
        $msg_password = "<span style='color:red;'>Data admin tidak ditemukan!</span>";
    } elseif ($admin['password'] !== md5($old)) {
        $msg_password = "<span style='color:red;'>Password lama salah!</span>";
    } elseif ($new !== $confirm) {
        $msg_password = "<span style='color:red;'>Password baru dan konfirmasi tidak cocok!</span>";
    } else {
        $hash = md5($new);
        $conn->query("UPDATE users SET password='$hash' WHERE username='admin'");
        $msg_password = "<span style='color:green;'>Password berhasil diubah!</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ubah Password Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 600px;
        }
        h2 {
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
        .alert {
            border-radius: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <a href="pengaturan" class="btn btn-secondary btn-custom mb-3">← Kembali ke Pengaturan</a>

        <h2>Ubah Password Admin</h2>
        <p class="text-muted text-center">Gunakan password yang kuat terdiri dari kombinasi huruf besar kecil, angka, dan tanda baca.</p>

        <div class="card">
            <div class="card-header">Pengaturan Password</div>
            <div class="card-body">
                <?php if ($msg_password) echo "<div class='alert alert-info'>$msg_password</div>"; ?>
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_password" class="form-control" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ulangi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required />
                    </div>
                    <div class="col-12">
                        <button type="submit" name="ubah_password" class="btn btn-success btn-custom w-100">Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
