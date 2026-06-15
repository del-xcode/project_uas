<?php
session_start();
$pageError = null;

require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $pageError = 'Email dan password wajib diisi.';
    } else {
        $statement = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $pageError = 'Email atau password salah.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $destination = $user['role'] === 'admin'
                ? app_url('admin/dashboard.php')
                : app_url('user/dashboard.php');

            header('Location: ' . $destination);
            exit;
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="auth-card mx-auto">
    <h1 class="h3 mb-3">Login</h1>
    <?php if ($pageError !== null): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
    <?php endif; ?>
    <form method="post" action="#" class="row g-3">
      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input class="form-control form-control-lg" type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label" for="password">Password</label>
        <input class="form-control form-control-lg" type="password" id="password" name="password" required>
      </div>
      <div class="col-12 d-grid">
        <button class="btn btn-primary btn-lg" type="submit">Masuk</button>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>