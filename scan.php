<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

if (isset($_GET['nisn'])) {
  $nisn = $_GET['nisn'];
  $tanggal = date("Y-m-d");
  $jam = date("H:i:s");

  $cekLibur = mysqli_query($conn, "SELECT * FROM hari_libur WHERE tanggal='$tanggal'");
  if (mysqli_num_rows($cekLibur) > 0) {
    echo "⛔ Hari ini libur!";
    exit;
  }

  $siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nisn='$nisn'");
  if (mysqli_num_rows($siswa) == 0) {
    echo "❌ Siswa tidak ditemukan.";
    exit;
  }
  $s = mysqli_fetch_assoc($siswa);

  $cekAbsen = mysqli_query($conn, "SELECT * FROM absensi WHERE siswa_id={$s['id']} AND tanggal='$tanggal'");

  if (mysqli_num_rows($cekAbsen) == 0) {
    // Belum ada absen → catat jam masuk
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam, status, jenis_sholat)
                         VALUES ({$s['id']}, '$tanggal', '$jam', 'H', 'Dhuhur')");
    echo "✅ Absen berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam hadir: $jam";
  } else {
    // Sudah ada absen
    $row = mysqli_fetch_assoc($cekAbsen);

    if ($row['status'] != 'H') {
      // Override status ke H jika sebelumnya bukan H
      mysqli_query($conn, "UPDATE absensi SET status='H', jam='$jam', jenis_sholat='Dhuhur'
                           WHERE id={$row['id']}");
      echo "✅ Absen berhasil (override): {$s['nama']} ({$s['kelas']})<br>🕒 Jam hadir: $jam";
    } else {
      // Sudah H, cek apakah jam pulang sudah terisi
      if (is_null($row['jam_pulang']) && $jam >= "09:00:00") {
        // Update jam pulang
        mysqli_query($conn, "UPDATE absensi SET jam_pulang='$jam'
                             WHERE id={$row['id']}");
        echo "✅ Pulang berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam pulang: $jam";
      } else {
        // Sudah absen masuk & pulang
        echo "ℹ️ {$s['nama']} sudah absen hari ini.<br>🕒 Jam hadir: {$row['jam']}";
        if (!is_null($row['jam_pulang'])) {
          echo "<br>🕒 Jam pulang: {$row['jam_pulang']}";
        }
      }
    }
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Scan QR Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://unpkg.com/html5-qrcode"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
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
      color: #007bff;
      font-weight: 600;
      margin-bottom: 1rem;
      text-align: center;
    }
    #reader {
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 1rem;
    }
    #result {
      max-height: 200px;
      overflow-y: auto;
      background: white;
      border-radius: 10px;
      padding: 1rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      font-size: 0.9rem;
      color: #333;
    }
    .btn-custom {
      border-radius: 10px;
      font-weight: 500;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <h2><i class="fas fa-qrcode me-2"></i>Scan QR Code Siswa</h2>
    <a href="dashboard" class="btn btn-outline-secondary btn-custom mb-3">
      <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
    </a>

    <div id="reader" style="width: 100%"></div>
    <div id="result" class="mb-3"></div>

    <!-- Suara beep -->
    <audio id="beepSound" src="beep.mp3" preload="auto"></audio>
  </div>

  <script>
    function onScanSuccess(qrMessage) {
      fetch("scan.php?nisn=" + encodeURIComponent(qrMessage))
        .then(res => res.text())
        .then(data => {
          let result = document.getElementById("result");
          let alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info mb-2";
          alertDiv.innerHTML = data;
          result.appendChild(alertDiv);

          // Mainkan suara beep
          document.getElementById("beepSound").play();

          // Scroll otomatis ke bawah
          result.scrollTop = result.scrollHeight;
        });
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
      "reader",
      {
        fps: 10,
        qrbox: { width: 300, height: 300 },
        supportedScanTypes: [Html5QrcodeSupportedFormats.QR_CODE],
        videoConstraints: {
          facingMode: "environment" // Gunakan kamera belakang
        }
      },
      false
    );
    html5QrcodeScanner.render(onScanSuccess);
  </script>
</body>
</html>


