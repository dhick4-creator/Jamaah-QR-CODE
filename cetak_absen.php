<?php
require('fpdf/fpdf.php');
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Data siswa
$siswaQuery = "SELECT * FROM siswa WHERE status='aktif'";
if ($kelas != '') {
    $siswaQuery .= " AND kelas = '$kelas'";
}
$siswaQuery .= " ORDER BY CAST(kelas AS UNSIGNED) ASC, kelas ASC, nama ASC";
$siswaResult = mysqli_query($conn, $siswaQuery);

// Data absensi
$absensi = [];
$absensiQuery = "SELECT a.*, s.nis, s.nama FROM absensi a 
                 JOIN siswa s ON a.siswa_id = s.id 
                 WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";
if ($kelas != '') {
    $absensiQuery .= " AND s.kelas = '$kelas'";
}
$resultAbsensi = mysqli_query($conn, $absensiQuery);
while ($row = mysqli_fetch_assoc($resultAbsensi)) {
    $sid = $row['siswa_id'];
    $tgl = (int)date('j', strtotime($row['tanggal']));
    $absensi[$sid][$tgl] = $row['status'];
}

// Hari libur
$libur = [];
$queryLibur = mysqli_query($conn, "SELECT tanggal FROM hari_libur");
while ($row = mysqli_fetch_assoc($queryLibur)) {
    $libur[] = $row['tanggal'];
}

// Profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kepala_sekolah, nip_kepala FROM profil_sekolah LIMIT 1"));



$tanggal_terakhir = date("j F Y", strtotime("$tahun-$bulan-" . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun)));

// --- Cetak PDF ---
// Ukuran kertas 21.5 x 33 cm = 215 x 330 mm, landscape
$bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$namaBulan = $bulanNama[$bulan - 1];
$pdf = new FPDF('L','mm',array(330,215));
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,"Rekap Absensi Bulan $namaBulan - ".($kelas ?: "Semua Kelas"),0,1,'C');

$pdf->SetFont('Arial','',10);

// Hitung total lebar tabel
$lebarNo = 10;
$lebarNis = 15;
$lebarNama = 40;
$lebarKelas = 15;
$lebarHari = 6;
$lebarRekap = 7;
$totalLebar = $lebarNo + $lebarNis + $lebarNama + $lebarKelas + ($jumlahHari * $lebarHari) + (3 * $lebarRekap);

// Pusatkan tabel
$marginKiri = (330 - $totalLebar) / 2;
if ($marginKiri < 0) $marginKiri = 0; // jika melebihi, mulai dari kiri
$pdf->SetX($marginKiri);

// Header tabel
$pdf->Cell($lebarNo,8,'No',1,0,'C');
$pdf->Cell($lebarNis,8,'NIS',1,0,'C');
$pdf->Cell($lebarNama,8,'Nama',1,0,'C');
$pdf->Cell($lebarKelas,8,'Kelas',1,0,'C');
for ($i = 1; $i <= $jumlahHari; $i++) {
    $pdf->Cell($lebarHari,8,$i,1,0,'C');
}
$pdf->Cell($lebarRekap,8,'S',1,0,'C');
$pdf->Cell($lebarRekap,8,'I',1,0,'C');
$pdf->Cell($lebarRekap,8,'A',1,1,'C');

// Isi tabel
$no = 1;
while ($siswa = mysqli_fetch_assoc($siswaResult)) {
    $sid = $siswa['id'];
    $pdf->SetX($marginKiri);
    $pdf->Cell($lebarNo,7,$no,1,0,'C');
// Ambil 4 karakter terakhir dari NIS
$nisAkhir = substr($siswa['nis'], -4);
$pdf->Cell($lebarNis,7,$nisAkhir,1,0,'C');

   // Batasi nama agar tidak menabrak kolom
$nama = $siswa['nama'];
$maxChar = 20; // sesuaikan agar muat di lebar 40 mm
if (strlen($nama) > $maxChar) {
    $nama = substr($nama, 0, $maxChar-3) . '...';
}
$pdf->Cell($lebarNama,7,$nama,1,0,'L');

$pdf->Cell($lebarKelas,7,$siswa['kelas'],1,0,'C');

    $countS = $countI = $countA = 0;
    for ($i = 1; $i <= $jumlahHari; $i++) {
        $val = $absensi[$sid][$i] ?? '';
        $tanggal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        $day = date('w', strtotime($tanggal));

        if ($val == '') {
            if ($day == 0 || in_array($tanggal, $libur)) {
                // Tanda libur merah
                $pdf->SetTextColor(255,0,0);
                $pdf->Cell($lebarHari,7,chr(149),1,0,'C'); // bullet point aman di FPDF
                $pdf->SetTextColor(0,0,0); // kembalikan warna hitam
            } else {
                $pdf->Cell($lebarHari,7,'',1,0,'C');
            }
        } else {
            $pdf->Cell($lebarHari,7,$val,1,0,'C');
            if ($val == 'S') $countS++;
            elseif ($val == 'I') $countI++;
            elseif ($val == 'A') $countA++;
        }
    }
    $pdf->Cell($lebarRekap,7,$countS,1,0,'C');
    $pdf->Cell($lebarRekap,7,$countI,1,0,'C');
    $pdf->Cell($lebarRekap,7,$countA,1,1,'C');

    $no++;
}

$pdf->Ln(10);

// Posisikan blok tanda tangan di bawah kolom tanggal 22
$posisiTanggal22 = $marginKiri + $lebarNo + $lebarNis + $lebarNama + $lebarKelas + (21 * $lebarHari);
$blokLebar = 40; // Lebar blok tanda tangan
$pdf->SetX($posisiTanggal22);

// Set font untuk tanda tangan
$pdf->SetFont('Arial','',12);

$pdf->Cell($blokLebar,8,"Probolinggo, ".$tanggal_terakhir,0,1,'L');

$pdf->SetX($posisiTanggal22);
$pdf->Cell($blokLebar,8,"Koordinator",0,1,'L');

$pdf->Ln(20);
$pdf->SetX($posisiTanggal22);
$pdf->Cell($blokLebar,8,$profil['kepala_sekolah'] ?? '....................................',0,1,'L');


$pdf->Output();
?>


