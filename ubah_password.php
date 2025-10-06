<?php
include 'session_check.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include "config.php";

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_pass = md5($_POST['old_password']);
    $new_pass = md5($_POST['new_password']);
    $confirm_pass = md5($_POST['confirm_password']);

    if ($new_pass !== $confirm_pass) {
        $message = "Password baru dan konfirmasi tidak cocok.";
    } else {
        $username = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            if ($data['password'] === $old_pass) {
                $stmtUpdate = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $stmtUpdate->bind_param("ss", $new_pass, $username);
                if ($stmtUpdate->execute()) {
                    $message = "Password berhasil diubah.";
                } else {
                    $message = "Gagal mengubah password.";
                }
                $stmtUpdate->close();
            } else {
                $message = "Password lama salah.";
            }
        } else {
            $message = "User tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ubah Password</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
    }
    .card {
      max-width: 500px;
      margin: auto;
      margin-top: 50px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card shadow">
      <div class="card-body">
        <h2 class="card-title text-center mb-4"><i class="fas fa-key text-primary me-2"></i>Ubah Password</h2>
        <?php if ($message): ?>
          <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label for="old_password" class="form-label">Password Lama</label>
            <input type="password" class="form-control" id="old_password" name="old_password" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Ubah Password</button>
          </div>
        </form>
        <div class="text-center mt-3">
          <a href="pengaturan" class="btn btn-secondary">Kembali</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
