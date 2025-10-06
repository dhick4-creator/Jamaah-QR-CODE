<?php
?>
// Aktifkan error reporting untuk debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

<?php
// install.php - Setup wizard for Absensi Jamaah QR
if (file_exists('config.php')) {
    echo '<h3>Aplikasi sudah terinstal. Hapus file install.php untuk keamanan.</h3>';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $name = $_POST['db_name'];

    $conn = @mysqli_connect($host, $user, $pass);
    if (!$conn) {
        $error = 'Koneksi ke MySQL gagal: ' . mysqli_connect_error();
    } else {
        // Buat database jika belum ada
        if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$name`")) {
            $error = 'Gagal membuat database: ' . mysqli_error($conn);
        } else {
            if (!mysqli_select_db($conn, $name)) {
                $error = 'Gagal memilih database: ' . mysqli_error($conn);
            } else {
                $sqlFile = 'db-absensi-jamaah-qr.sql';
                if (!file_exists($sqlFile)) {
                    $error = 'File ' . $sqlFile . ' tidak ditemukan.';
                } else {
                    $sql = file_get_contents($sqlFile);
                    if ($sql) {
                        if (mysqli_multi_query($conn, $sql)) {
                            // Selesaikan semua query multi
                            do { } while (mysqli_more_results($conn) && mysqli_next_result($conn));
                            // Simpan config.php
                            $config = "<?php\n$" . "conn = mysqli_connect('{$host}', '{$user}', '{$pass}', '{$name}');\nif (!\$conn) { die('Koneksi gagal'); }\n?>";
                            file_put_contents('config.php', $config);
                            echo '<h3>Instalasi berhasil! Silakan hapus file install.php lalu login.</h3>';
                            exit;
                        } else {
                            $error = 'Gagal membuat tabel: ' . mysqli_error($conn);
                        }
                    } else {
                        $error = 'File SQL kosong.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Instalasi Absensi Jamaah QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="mb-4">Instalasi Absensi Jamaah QR</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Host Database</label>
                            <input type="text" name="db_host" class="form-control" required value="localhost">
                        </div>
                        <div class="mb-3">
                            <label>User Database</label>
                            <input type="text" name="db_user" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password Database</label>
                            <input type="password" name="db_pass" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Nama Database</label>
                            <input type="text" name="db_name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Instal Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
