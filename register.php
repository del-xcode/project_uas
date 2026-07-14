<?php
session_start();
require_once __DIR__ . '/config/app.php';
require_csrf();
$pageError = null;
$pageSuccess = null;

require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['nomor_hp'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $pageError = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pageError = 'Format email tidak valid.';
    } else {
        $statement = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        if ($statement->fetch()) {
            $pageError = 'Email sudah terdaftar.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStatement = $pdo->prepare(
                'INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, :role)'
            );
            $insertStatement->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashedPassword,
                'role' => 'user',
            ]);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'user';

            header('Location: ' . app_url('user/dashboard.php'));
            exit;
        }
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="auth-card mx-auto">
    <h1 class="h3 mb-3 d-flex align-items-center justify-content-center">
      <i class="bi bi-person-plus-fill text-teal me-2"></i>
      <span>Daftar Akun</span>
    </h1>
    <?php if ($pageError !== null): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
    <?php endif; ?>
    <form method="post" action="#" class="row g-3">
      <?php echo csrf_input(); ?>
      <div class="col-12">
        <label class="form-label fw-semibold" for="nama"><i class="bi bi-person-fill text-teal me-1"></i> Nama Lengkap</label>
        <input class="form-control" type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold" for="email"><i class="bi bi-envelope-fill text-teal me-1"></i> Email</label>
        <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold" for="nomor_hp"><i class="bi bi-telephone-fill text-teal me-1"></i> Nomor HP</label>
        <input class="form-control" type="text" id="nomor_hp" name="nomor_hp" value="<?php echo htmlspecialchars($_POST['nomor_hp'] ?? ''); ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold" for="password"><i class="bi bi-lock-fill text-teal me-1"></i> Password</label>
        <input class="form-control" type="password" id="password" name="password" required>
      </div>
      <div class="col-12 d-grid">
        <button class="btn btn-primary btn-lg" type="submit">Daftar</button>
      </div>
      <div class="col-12 text-center mt-3">
        <span class="text-secondary small">Sudah punya akun? <a href="<?php echo htmlspecialchars(app_url('login.php')); ?>" class="text-teal fw-semibold text-decoration-none">Masuk Di Sini</a></span>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>