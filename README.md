# Absensi Jamaah QR - Version 1.2.6

## Summary of Changes in Version 1.2.6
- Fixed require path for phpqrcode library in `siswa.php`, `siswa_keluar.php`, and `import_siswa.php` to resolve missing file errors.
- Removed auto-download of phpqrcode library due to broken URL; recommend using Composer for dependency management.
- Updated version to 1.2.6.

## Installation Instructions
1. Ensure you have Composer installed on your server.
2. Run `composer install` in the project root directory to install dependencies, including phpqrcode.
3. Verify that the `vendor/phpqrcode/phpqrcode/qrlib.php` file exists after installation.

## Testing Recommendations
- Test the main pages: `siswa.php`, `siswa_keluar.php`, and `import_siswa.php`.
- Verify QR code generation and display.
- Test import functionality with valid and invalid Excel files.
- Confirm user account creation and updates for students.

## Notes
- The previous auto-download mechanism for the phpqrcode library has been removed due to reliability issues.
- All require statements for phpqrcode now point to the Composer-installed path.
