<?php
include 'session_check.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include "config.php";

$message = "";

// Handle delete siswa
if (isset($_GET['delete_siswa'])) {
    $conn->query("DELETE FROM users WHERE role = 'siswa'");
    $message = "Semua user siswa berhasil dihapus.";
    header("Location: kelola_user.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "User berhasil dihapus.";
    } else {
        $message = "Gagal menghapus user.";
    }
    $stmt->close();
    header("Location: kelola_user.php");
    exit;
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? md5($_POST['password']) : null;
    $role = $_POST['role'];

    if (empty($username) || empty($role)) {
        $message = "Username dan role harus diisi.";
    } else {
        // Check if username exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username sudah ada.";
        } else {
            if ($password) {
                $stmtUpdate = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmtUpdate->bind_param("sssi", $username, $password, $role, $id);
            } else {
                $stmtUpdate = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmtUpdate->bind_param("ssi", $username, $role, $id);
            }
            if ($stmtUpdate->execute()) {
                $message = "User berhasil diperbarui.";
            } else {
                $message = "Gagal memperbarui user.";
            }
            $stmtUpdate->close();
        }
        $stmt->close();
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kelola User</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
    }
    .table th {
      background: #007bff;
      color: white;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <a href="pengaturan" class="btn btn-secondary mb-3">← Kembali ke Pengaturan</a>

    <h2 class="mb-4"><i class="fas fa-users text-primary me-2"></i>Kelola User</h2>

    <a href="?delete_siswa=1" class="btn btn-danger mb-3" onclick="return confirm('Yakin hapus semua user siswa?')">Hapus Semua User Siswa</a>

    <?php if ($message): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
              <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                  <?php if ($user['username'] !== $_SESSION['username']): ?>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-role="<?php echo $user['role']; ?>"><i class="fas fa-edit"></i> Edit</button>
                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')"><i class="fas fa-trash"></i> Hapus</a>
                  <?php else: ?>
                    <span class="text-muted">User saat ini</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="mb-3">
              <label for="editUsername" class="form-label">Username</label>
              <input type="text" class="form-control" id="editUsername" name="username" required>
            </div>
            <div class="mb-3">
              <label for="editPassword" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
              <input type="password" class="form-control" id="editPassword" name="password">
            </div>
            <div class="mb-3">
              <label for="editRole" class="form-label">Role</label>
              <select class="form-control" id="editRole" name="role" required>
                <option value="admin">Admin</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_user" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const username = button.getAttribute('data-username');
      const role = button.getAttribute('data-role');

      document.getElementById('editId').value = id;
      document.getElementById('editUsername').value = username;
      document.getElementById('editRole').value = role;
      document.getElementById('editPassword').value = '';
    });
  </script>
</body>
</html>
