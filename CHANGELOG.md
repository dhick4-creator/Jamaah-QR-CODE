#
## [1.2.6] - 2025-10-07
### Fixed
- Fixed require path for phpqrcode library in siswa.php, siswa_keluar.php, and import_siswa.php to resolve missing file errors.
- Removed auto-download of phpqrcode library due to broken URL; recommend using composer for dependency management.
- Updated version to 1.2.6.

## [1.2.5] - 2025-10-06
### Fixed
- Perbaikan instalasi otomatis library phpqrcode untuk menghindari error pada server hosting.
#
## [1.2.4] - 2025-10-06
### Added
- Menambahkan dependency management dengan Composer untuk library phpqrcode.
### Changed
- Update progress di update_aplikasi.php sekarang menampilkan persentase saja tanpa listing file.
### Fixed
- Perbaikan error require vendor/phpqrcode/qrlib.php dengan instalasi otomatis library dari GitHub jika belum ada.
#
## [1.2.3] - 2025-10-06
### Changed
- Hapus seluruh tampilan versi aplikasi di menu dashboard.
#
## [1.2.2] - 2025-10-06
### Changed
- Hilangkan tampilan versi aplikasi di menu dashboard.
### Fixed
- Perbaikan minor lainnya.
#
## [1.2.1] - 2025-10-06
### Fixed
- Perbaikan install.php: error reporting aktif, pengecekan file SQL, dan pesan error lebih jelas.
#
## [1.2.0] - 2025-10-06
### Added
- Fitur install.php: instalasi otomatis database dan config saat pertama kali upload ke hosting.
# Changelog

## [1.1.0] - 2025-10-06
### Changed
- Branding aplikasi di seluruh UI, metadata, dan dokumentasi diubah menjadi "Absensi Jamaah QR".
- Pesan commit awal dan seluruh riwayat git sudah disesuaikan dengan nama baru.
- Update file `version.json` ke versi 1.1.0.
- Penyesuaian label, judul, dan komentar di seluruh file aplikasi agar konsisten dengan branding baru.

### Fixed
- Konsistensi tampilan dan metadata aplikasi.

---

## [1.0.0] - Initial release
- Rilis awal aplikasi absensi QR custom.
