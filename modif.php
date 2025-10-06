<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
header("Location: index");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
  <title>Dashboard Admin Absensi QR Modif</title>
=======
  <title>Dashboard Admin Absensi Jamaah QR (Modif)</title>
>>>>>>> 221b6cccffe028aa08e087dedef1b34cc07599ab
  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f4;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Running text modern */
    .marquee-container {
      background: #ff9800;
      overflow: hidden;
      white-space: nowrap;
      box-sizing: border-box;
      padding: 10px 0;
    }

    .marquee-text {
      display: inline-block;
      padding-left: 100%;
      animation: marquee 15s linear infinite;
      color: white;
      font-weight: bold;
      font-size: 16px;
    }

    @keyframes marquee {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }

    header {
      background-color: #4CAF50;
      color: white;
      padding: 15px;
      text-align: center;
    }

    h2 {
      margin: 20px;
      text-align: center;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 400px;
      margin: 20px auto;
    }

    li a {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background-color: #fff;
      color: #333;
      text-decoration: none;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: background-color 0.3s ease;
      text-align: center;
      border: 4px solid transparent; /* default */
      font-weight: bold;
    }

    li a:hover {
      background-color: #e0e0e0;
    }

    /* Border warna khusus */
    .siswa { border-color: orange; }
    .scan { border-color: green; }
    .sia { border-color: blue; }
    .jam { border-color: purple; }
    .rekap { border-color: black; }
    .grafik { border-color: black; }
    .prosentase { border-color: black; }
    .libur { border-color: red; }
    .excel { border-color: green; }
    .wa { border-color: green; }
    .profil { border-color: green; }
    .hapus { border-color: red; }
    .restore { border-color: purple; }
    .backup { border-color: blue; }
    .logout { border-color: red; }

    @media (min-width: 600px) {
      ul {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
      }

      li {
        flex: 1 1 40%;
        margin: 5px;
      }
    }

    footer {
      margin-top: auto;
      background: #333;
      color: white;
      text-align: center;
      padding: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <!-- Running Text Modern -->
  <div class="marquee-container">
    <span class="marquee-text">
      Menu ini adalah menu tambahan permintaan dari teman-teman yang menggunakan aplikasi ni
    </span>
  </div>

  <header>
    <h1>Dashboard Admin Menu Tambahan</h1>
  </header>

  <h2>Menu</h2>
  <ul>
    
    <li><a href="rekap_bulanan_5_hari" class="rekap"><i class="fa-solid fa-calendar-days"></i> Rekap Bulanan 5 Hari Kerja</a></li>
	<li><a href="rekap_bulanan_jumat" class="rekap"><i class="fa-solid fa-calendar-days"></i> Rekap Bulanan Libur Jumat</a></li>
	  <li><a href="raport" class="rekap"><i class="fa-solid fa-calendar-days"></i> Rekap SIA untuk raport</a></li>
<li><a href="rfid.html" class="scan"><i class="fa-solid fa-id-card"></i> Scan Kartu RF ID</a></li>
	  <li><a href="pengaturan_rfid" class="siswa"><i class="fa-solid fa-id-card"></i> Pengaturan RF ID</a></li>
<li><a href="scanner" class="scan"><i class="fa-solid fa-border-none"></i> Alat Scanner Minimarket</a></li>
	  <li><a href="https://chat.whatsapp.com/KtdYP6nx3eZLVhqJkQ1Zbs?mode=ac_t" class="wa"><i class="fa-brands fa-whatsapp"></i> Grup WA</a></li>
    
    <li><a href="dashboard" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Dashboard Utama</a></li>
  </ul>

  <footer>
    Versi Aplikasi: 1.0.0
  </footer>
</body>
</html>


