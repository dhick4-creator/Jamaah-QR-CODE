<?php
include 'session_check.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include "config.php";

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    if (empty($username) || empty($_POST['password']) || empty($role)) {
        $message = "Semua field harus diisi.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username sudah ada.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmtInsert->bind_param("sss", $username, $password, $role);
            if ($stmtInsert->execute()) {
                $message = "User baru berhasil ditambahkan.";
            } else {
                $message = "Gagal menambahkan user.";
            }
            $stmtInsert->close();
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
  <title>Tambah User Baru</title>

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
        <h2 class="card-title text-center mb-4"><i class="fas fa-user-plus text-primary me-2"></i>Tambah User Baru</h2>
        <?php if ($message): ?>
          <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-control" id="role" name="role" required>
              <option value="">Pilih Role</option>
              <option value="admin">Admin</option>
              <option value="guru">Guru</option>
            </select>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Tambah User</button>
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
