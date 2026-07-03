<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen User';
$pageError = null;
$pageSuccess = null;
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);

// Handing User Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $targetUserId = (int) ($_POST['user_id'] ?? 0);

    if ($targetUserId <= 0) {
        $pageError = 'User tidak valid.';
    } elseif ($targetUserId === $currentAdminId) {
        $pageError = 'Anda tidak dapat mengubah role atau menghapus akun Anda sendiri.';
    } else {
        if ($action === 'change_role') {
            $newRole = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            $updateStatement = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $updateStatement->execute(['role' => $newRole, 'id' => $targetUserId]);
            $pageSuccess = 'Role pengguna berhasil diperbarui.';
        } elseif ($action === 'delete_user') {
            $deleteStatement = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $deleteStatement->execute(['id' => $targetUserId]);
            $pageSuccess = 'Pengguna berhasil dihapus dari sistem.';
        }
    }
}

// Handling Search (GET)
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $userStatement = $pdo->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE name LIKE :search OR email LIKE :search ORDER BY id DESC');
    $userStatement->execute(['search' => "%{$search}%"]);
} else {
    $userStatement = $pdo->query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC');
}
$users = $userStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1 d-flex align-items-center">
          <i class="bi bi-people-fill text-teal me-2"></i>
          <span>Manajemen User</span>
        </h1>
        <p class="text-secondary mb-0">Kelola akun pengguna, ubah hak akses, dan hapus akun.</p>
      </div>
    </div>

    <?php if ($pageError !== null): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
    <?php endif; ?>
    <?php if ($pageSuccess !== null): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="get" class="row g-2 mb-4">
      <div class="col-md-5">
        <input type="text" class="form-control" name="search" placeholder="Cari nama atau email..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Cari</button>
      </div>
      <?php if ($search !== ''): ?>
        <div class="col-md-2 d-grid">
          <a href="users.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      <?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
      <div class="alert alert-info mb-0">Tidak ada user ditemukan.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Dibuat</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo (int) $user['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td>
                  <span class="badge <?php echo $user['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-primary'; ?>">
                    <?php echo htmlspecialchars($user['role']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td class="text-end">
                  <?php if ((int)$user['id'] !== $currentAdminId): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah role user ini?')">
                      <?php echo csrf_input(); ?>
                      <input type="hidden" name="action" value="change_role">
                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                      <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Ubah ke <?php echo $user['role'] === 'admin' ? 'User' : 'Admin'; ?>
                      </button>
                    </form>
                    
                    <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini? Semua data booking dan kendaraan terkait juga akan terhapus.')">
                      <?php echo csrf_input(); ?>
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                  <?php else: ?>
                    <span class="text-secondary small">Akun Anda</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>