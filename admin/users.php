<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen User';

$userStatement = $pdo->query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC');
$users = $userStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <h1 class="h3 mb-3">Manajemen User</h1>

    <?php if (empty($users)): ?>
      <div class="alert alert-info mb-0">Belum ada user.</div>
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
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo (int) $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td><span class="badge <?php echo $user['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-primary'; ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>