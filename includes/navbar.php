<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
$dashboardUrl = $userRole === 'admin'
    ? app_url('admin/dashboard.php')
    : app_url('user/dashboard.php');
$bookingUrl = app_url('user/booking.php');
$profileUrl = app_url('user/profile.php');
$ordersUrl = app_url('user/orders.php');
$adminServicesUrl = app_url('admin/services.php');
$adminOrdersUrl = app_url('admin/orders.php');
$adminUsersUrl = app_url('admin/users.php');
$adminPaymentsUrl = app_url('admin/payments.php');
$adminReportsUrl = app_url('admin/reports.php');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo htmlspecialchars(app_url('index.php')); ?>">CarWash</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('index.php')); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('about.php')); ?>">Tentang</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('services.php')); ?>">Layanan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('contact.php')); ?>">Kontak</a></li>
        <?php if ($isLoggedIn): ?>
          <?php if ($userRole === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($adminUsersUrl); ?>">User</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($adminServicesUrl); ?>">Layanan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($adminOrdersUrl); ?>">Booking</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($adminPaymentsUrl); ?>">Pembayaran</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($adminReportsUrl); ?>">Laporan</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($profileUrl); ?>">Kendaraan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($bookingUrl); ?>">Booking</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($ordersUrl); ?>">Riwayat</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($dashboardUrl); ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('logout.php')); ?>">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('login.php')); ?>">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>