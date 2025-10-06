<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nisn'])) {
  $nisn = $_POST['nisn'];
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
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam, status) 
                         VALUES ({$s['id']}, '$tanggal', '$jam', 'H')");
    echo "✅ Absen berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam hadir: $jam";
  } else {
    $row = mysqli_fetch_assoc($cekAbsen);

    if (is_null($row['jam_pulang']) && $jam >= "09:00:00") {
      mysqli_query($conn, "UPDATE absensi SET jam_pulang='$jam' 
                           WHERE id={$row['id']}");
      echo "✅ Pulang berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam pulang: $jam";
    } else {
      echo "ℹ️ {$s['nama']} sudah absen hari ini.<br>🕒 Jam hadir: {$row['jam']}";
      if (!is_null($row['jam_pulang'])) {
        echo "<br>🕒 Jam pulang: {$row['jam_pulang']}";
      }
    }
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Absensi Jamaah QR - Scanner</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { text-align:center; background:#f8f9fa; }
    .scanner-hint {
      margin-top: 20px;
      font-size: 1.4rem;
      font-weight: bold;
      color: #007bff;
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.1); opacity: 0.6; }
      100% { transform: scale(1); opacity: 1; }
    }
    .arrow-down {
      margin-top: 15px;
      font-size: 3rem;
      color: #dc3545;
      animation: bounce 1s infinite;
    }
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(10px); }
    }
    #result { max-height:300px; overflow-y:auto; margin-top:20px; }
    #nisnInput { opacity:0; position:absolute; left:-9999px; }
  </style>
</head>
<body class="container mt-5">
  <h2>🔍 Scan QR / Barcode Siswa</h2>
  <a href="dashboard" class="btn btn-secondary mb-3">← Kembali</a>

  <div class="scanner-hint">Tempelkan Kartu pada Scanner</div>
  <div class="arrow-down">⬇️</div>

  <form id="scanForm" method="POST">
    <input type="text" name="nisn" id="nisnInput" autofocus>
  </form>

  <div id="result"></div>

  <audio id="beepSound" src="beep.mp3" preload="auto"></audio>

  <script>
    const input = document.getElementById("nisnInput");
    const form = document.getElementById("scanForm");
    const result = document.getElementById("result");

    // Fokus otomatis ke input (agar scanner langsung ngetik ke sini)
    setInterval(() => { input.focus(); }, 500);

    input.addEventListener("change", () => {
      let formData = new FormData(form);
      fetch("scan.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.text())
      .then(data => {
        let alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-info mb-2";
        alertDiv.innerHTML = data;
        result.appendChild(alertDiv);

        // Suara beep
        document.getElementById("beepSound").play();

        // Scroll ke bawah
        result.scrollTop = result.scrollHeight;

        // Reset input untuk scan berikutnya
        input.value = "";
        input.focus();
      });
    });
  </script>
</body>
</html>


