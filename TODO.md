# TODO: Fix phpqrcode Library Issue

## Problem
The auto-download of phpqrcode library from GitHub is failing because the URLs are returning 404 errors. The repositories are either empty or the raw URLs are incorrect.

## Solution
Remove the auto-download code and require manual upload of the qrlib.php file to vendor/phpqrcode/qrlib.php.

## Files Updated
- import_siswa.php: Removed auto-download, added manual upload requirement
- siswa.php: Removed auto-download, added manual upload requirement
- siswa_keluar.php: Removed auto-download, added manual upload requirement

## Next Steps
1. Download the qrlib.php file from a reliable source
2. Upload it to the hosting server at vendor/phpqrcode/phpqrcode/qrlib.php
3. Test the application to ensure QR code generation works

## Additional Changes
- [x] Updated login error handling: When login fails, show error message and redirect back to login page
  - Modified cek.php to redirect to index.php?error=1 on login failure
  - Modified index.php to display error alert when error=1 parameter is present

## Alternative Sources for qrlib.php
- SourceForge: https://sourceforge.net/projects/phpqrcode/ (download the ZIP file, extract qrlib.php)
- Or search for "phpqrcode qrlib.php download" and download from a trusted source
- Ensure the file contains the QRcode class with png() method
