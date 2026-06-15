<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();

require __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard User';
$userId = (int) ($_SESSION['user_id'] ?? 0);

$vehicleCountStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM vehicles WHERE user_id = :user_id');
$vehicleCountStatement->execute(['user_id' => $userId]);
$vehicleCount = (int) ($vehicleCountStatement->fetch()['total'] ?? 0);

$bookingCountStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM bookings WHERE user_id = :user_id');
$bookingCountStatement->execute(['user_id' => $userId]);
$bookingCount = (int) ($bookingCountStatement->fetch()['total'] ?? 0);

$pendingPaymentStatement = $pdo->prepare(
    'SELECT COUNT(*) AS total
     FROM payments p
     INNER JOIN bookings b ON b.id = p.booking_id
     WHERE b.user_id = :user_id AND p.payment_status = "pending"'
);
$pendingPaymentStatement->execute(['user_id' => $userId]);
$pendingPaymentCount = (int) ($pendingPaymentStatement->fetch()['total'] ?? 0);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Dashboard User</h1>
        <p class="text-secondary mb-0">Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('user/profile.php')); ?>">Profil Kendaraan</a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>">Booking Baru</a>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <div class="stat-card h-100">
          <span class="stat-label">Kendaraan Tersimpan</span>
          <strong><?php echo $vehicleCount; ?></strong>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card h-100">
          <span class="stat-label">Total Booking</span>
          <strong><?php echo $bookingCount; ?></strong>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card h-100">
          <span class="stat-label">Pembayaran Pending</span>
          <strong><?php echo $pendingPaymentCount; ?></strong>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>