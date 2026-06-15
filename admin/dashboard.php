<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard Admin';
$totalUsersStatement = $pdo->query('SELECT COUNT(*) AS total FROM users');
$totalUsers = (int) ($totalUsersStatement->fetch()['total'] ?? 0);

$totalBookingsStatement = $pdo->query('SELECT COUNT(*) AS total FROM bookings');
$totalBookings = (int) ($totalBookingsStatement->fetch()['total'] ?? 0);

$pendingBookingsStatement = $pdo->query('SELECT COUNT(*) AS total FROM bookings WHERE status = "pending"');
$pendingBookings = (int) ($pendingBookingsStatement->fetch()['total'] ?? 0);

$pendingPaymentsStatement = $pdo->query('SELECT COUNT(*) AS total FROM payments WHERE payment_status = "pending"');
$pendingPayments = (int) ($pendingPaymentsStatement->fetch()['total'] ?? 0);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Dashboard Admin</h1>
        <p class="text-secondary mb-0">Ringkasan operasional sistem.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">Kelola Booking</a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('admin/services.php')); ?>">Kelola Layanan</a>
        <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars(app_url('admin/reports.php')); ?>">Laporan</a>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Total User</span>
          <strong><?php echo $totalUsers; ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Total Booking</span>
          <strong><?php echo $totalBookings; ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Booking Pending</span>
          <strong><?php echo $pendingBookings; ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Pembayaran Pending</span>
          <strong><?php echo $pendingPayments; ?></strong>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>