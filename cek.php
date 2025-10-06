<?php
session_start();
include 'config.php';

// Ambil input
$user = $_POST['username'];
$pass = md5($_POST['password']); // sebaiknya nanti diganti password_hash()

// ✅ Gunakan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? LIMIT 1");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    // Simpan session dasar
    $_SESSION['username'] = $user;
    $_SESSION['role'] = $data['role'];
    $_SESSION['last_activity'] = time();

    // Cek role untuk redirect
    if ($data['role'] === 'admin') {
        header("Location: dashboard");
    } elseif ($data['role'] === 'guru') {
        header("Location: dashboard_guru.php");
    } elseif ($data['role'] === 'siswa') {
        // Cari id siswa dari tabel siswa berdasarkan nisn (username)
        $stmtSiswa = $conn->prepare("SELECT id FROM siswa WHERE nisn = ? LIMIT 1");
        $stmtSiswa->bind_param("s", $user);
        $stmtSiswa->execute();
        $resSiswa = $stmtSiswa->get_result();

        if ($rowSiswa = $resSiswa->fetch_assoc()) {
            $_SESSION['siswa_id'] = $rowSiswa['id'];
        } else {
            echo "Data siswa tidak ditemukan. Hubungi admin.";
            exit;
        }

        header("Location: dashboard_siswa.php");
    } else {
        echo "Role tidak dikenali";
    }
    exit;

} else {
    header("Location: index.php?error=1");
    exit;
}
?>


