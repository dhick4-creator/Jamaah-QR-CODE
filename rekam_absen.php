<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

if (isset($_GET['nisn'])) {
    $nisn = $_GET['nisn'];
    $tanggal = date("Y-m-d");
    $jam = date("H:i:s");

    // Cek libur
    $cekLibur = mysqli_query($conn, "SELECT * FROM hari_libur WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cekLibur) > 0) {
        echo json_encode(["status" => "info", "message" => "⛔ Hari ini libur!"]);
        exit;
    }

    // Ambil data siswa
    $siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nisn='$nisn'");
    if (mysqli_num_rows($siswa) == 0) {
        echo json_encode(["status" => "error", "message" => "❌ Siswa tidak ditemukan."]);
        exit;
    }
    $s = mysqli_fetch_assoc($siswa);

    // Normalisasi nomor WA
    $no_wa = "";
    if (!empty($s['no_wa'])) {
        $no_wa = preg_replace('/[^0-9]/', '', $s['no_wa']);
        if (substr($no_wa, 0, 1) == "0") {
            $no_wa = "62" . substr($no_wa, 1);
        }
    }

    $waLink = "";

    // Cek absen
    $cekAbsen = mysqli_query($conn, "SELECT * FROM absensi WHERE siswa_id={$s['id']} AND tanggal='$tanggal'");
    if (mysqli_num_rows($cekAbsen) == 0) {
        // ✅ Belum ada absen → catat jam hadir
        mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam, status) 
                             VALUES ({$s['id']}, '$tanggal', '$jam', 'H')");
        
        $msg = "✅ Absen berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam hadir: $jam";
        $pesan = "Halo, {$s['nama']} dari kelas {$s['kelas']} sudah *hadir* pada $tanggal jam $jam.";
        
        if (!empty($no_wa)) {
            $waLink = "https://wa.me/$no_wa?text=" . urlencode($pesan);
        }

    } else {
        // ✅ Sudah pernah absen → cek apakah sudah ada jam_pulang
        $row = mysqli_fetch_assoc($cekAbsen);

        if (is_null($row['jam_pulang']) && $jam >= "09:00:00") {
            // Update jam pulang
            mysqli_query($conn, "UPDATE absensi SET jam_pulang='$jam' WHERE id={$row['id']}");
            
            $msg = "✅ Pulang berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam pulang: $jam";
            $pesan = "Halo, {$s['nama']} dari kelas {$s['kelas']} sudah *pulang* pada $tanggal jam $jam.";
            
            if (!empty($no_wa)) {
                $waLink = "https://wa.me/$no_wa?text=" . urlencode($pesan);
            }

        } else {
            // Sudah absen masuk dan pulang
            $msg = "ℹ️ {$s['nama']} sudah absen hari ini.<br>🕒 Jam hadir: {$row['jam']}";
            if (!is_null($row['jam_pulang'])) {
                $msg .= "<br>🕒 Jam pulang: {$row['jam_pulang']}";
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => $msg
    ]);
    exit;
}
?>


