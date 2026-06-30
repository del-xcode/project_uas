<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();

require __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard User';
$userId = (int) ($_SESSION['user_id'] ?? 0);

// 1. Booking Aktif (status: pending atau process)
$activeBookingStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM bookings WHERE user_id = :user_id AND status IN ("pending", "process")');
$activeBookingStatement->execute(['user_id' => $userId]);
$activeBookingCount = (int) ($activeBookingStatement->fetch()['total'] ?? 0);

// 2. Riwayat Booking (Total Booking & data booking terbaru)
$totalBookingStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM bookings WHERE user_id = :user_id');
$totalBookingStatement->execute(['user_id' => $userId]);
$totalBookingCount = (int) ($totalBookingStatement->fetch()['total'] ?? 0);

$recentBookingsStatement = $pdo->prepare(
    'SELECT b.id, b.booking_date, b.booking_time, b.status AS booking_status, s.service_name, s.price, p.id AS payment_id, p.payment_status
     FROM bookings b
     INNER JOIN services s ON s.id = b.service_id
     LEFT JOIN payments p ON p.booking_id = b.id
     WHERE b.user_id = :user_id
     ORDER BY b.id DESC
     LIMIT 5'
);
$recentBookingsStatement->execute(['user_id' => $userId]);
$recentBookings = $recentBookingsStatement->fetchAll();

// 3. Status Pembayaran
$paymentStatusStatement = $pdo->prepare(
    'SELECT p.payment_status, COUNT(*) AS count
     FROM payments p
     INNER JOIN bookings b ON b.id = p.booking_id
     WHERE b.user_id = :user_id
     GROUP BY p.payment_status'
);
$paymentStatusStatement->execute(['user_id' => $userId]);
$paymentStatuses = $paymentStatusStatement->fetchAll();

$paymentCounts = ['pending' => 0, 'paid' => 0, 'failed' => 0, 'expired' => 0];
foreach ($paymentStatuses as $row) {
    if (isset($paymentCounts[$row['payment_status']])) {
        $paymentCounts[$row['payment_status']] = (int) $row['count'];
    }
}

// 4. Profil Kendaraan (Daftar Kendaraan & Total Kendaraan)
$vehicleCountStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM vehicles WHERE user_id = :user_id');
$vehicleCountStatement->execute(['user_id' => $userId]);
$vehicleCount = (int) ($vehicleCountStatement->fetch()['total'] ?? 0);

$vehiclesStatement = $pdo->prepare('SELECT id, vehicle_type, brand, plate_number FROM vehicles WHERE user_id = :user_id ORDER BY id DESC LIMIT 3');
$vehiclesStatement->execute(['user_id' => $userId]);
$vehiclesList = $vehiclesStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <!-- Welcome Card -->
  <div class="content-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h1 class="h3 mb-1">Dashboard User</h1>
        <p class="text-secondary mb-0">Selamat datang kembali, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Pelanggan'); ?></strong>!</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('user/profile.php')); ?>">Kelola Kendaraan</a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>">Booking Baru</a>
      </div>
    </div>
  </div>

  <!-- Row 1: Summary Cards (Widgets) -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-calendar2-week text-warning fs-3 mb-2 d-block"></i>
          <span class="stat-label">Booking Aktif</span>
        </div>
        <strong class="text-warning"><?php echo $activeBookingCount; ?></strong>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-journal-check text-teal fs-3 mb-2 d-block"></i>
          <span class="stat-label">Total Booking</span>
        </div>
        <strong class="text-teal"><?php echo $totalBookingCount; ?></strong>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-credit-card text-danger fs-3 mb-2 d-block"></i>
          <span class="stat-label">Belum Dibayar</span>
        </div>
        <strong class="text-danger"><?php echo $paymentCounts['pending']; ?></strong>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-car-front text-teal fs-3 mb-2 d-block"></i>
          <span class="stat-label">Kendaraan</span>
        </div>
        <strong class="text-teal"><?php echo $vehicleCount; ?></strong>
      </div>
    </div>
  </div>

  <!-- Row 2: Detailed View -->
  <div class="row g-4">
    <!-- Riwayat Booking (Terbaru) -->
    <div class="col-lg-7">
      <div class="content-card p-4 h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 fw-bold mb-0">Booking Terbaru</h2>
          <a class="btn btn-sm btn-link text-teal text-decoration-none" href="<?php echo htmlspecialchars(app_url('user/orders.php')); ?>">Lihat Semua →</a>
        </div>
        
        <?php if (empty($recentBookings)): ?>
          <div class="alert alert-info py-3 mb-0">Anda belum pernah melakukan booking layanan.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Layanan</th>
                  <th>Tanggal/Jam</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentBookings as $booking): ?>
                  <tr>
                    <td>
                      <span class="fw-semibold"><?php echo htmlspecialchars($booking['service_name']); ?></span><br>
                      <small class="text-secondary">Rp <?php echo number_format((float)$booking['price'], 0, ',', '.'); ?></small>
                    </td>
                    <td>
                      <small><?php echo htmlspecialchars($booking['booking_date'] . ' / ' . $booking['booking_time']); ?></small>
                    </td>
                    <td>
                      <?php
                      $bookingBadge = 'text-bg-secondary';
                      if ($booking['booking_status'] === 'process') $bookingBadge = 'text-bg-info';
                      elseif ($booking['booking_status'] === 'done') $bookingBadge = 'text-bg-success';
                      elseif ($booking['booking_status'] === 'cancelled') $bookingBadge = 'text-bg-danger';
                      ?>
                      <span class="badge <?php echo $bookingBadge; ?>"><?php echo htmlspecialchars($booking['booking_status']); ?></span>
                    </td>
                    <td>
                      <?php if ($booking['booking_status'] !== 'cancelled' && $booking['payment_status'] !== 'paid' && !empty($booking['payment_id'])): ?>
                        <a class="btn btn-sm btn-primary py-1 px-2" href="<?php echo htmlspecialchars(app_url('user/payment_checkout.php?payment_id=' . (int) $booking['payment_id'])); ?>">Bayar</a>
                      <?php else: ?>
                        <small class="text-secondary"><?php echo htmlspecialchars($booking['payment_status'] ?? 'pending'); ?></small>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profil Kendaraan & Status Pembayaran ringkasan -->
    <div class="col-lg-5">
      <div class="row g-4">
        <!-- Profil Kendaraan Widget -->
        <div class="col-12">
          <div class="content-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="h5 fw-bold mb-0">Kendaraan Saya</h2>
              <a class="btn btn-sm btn-link text-teal text-decoration-none" href="<?php echo htmlspecialchars(app_url('user/profile.php')); ?>">Kelola →</a>
            </div>
            
            <?php if (empty($vehiclesList)): ?>
              <div class="alert alert-warning py-3 mb-0">Belum ada kendaraan yang terdaftar. <a href="<?php echo htmlspecialchars(app_url('user/profile.php')); ?>">Daftarkan sekarang</a> untuk melakukan booking.</div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($vehiclesList as $v): ?>
                  <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                    <div>
                      <span class="fw-semibold"><?php echo htmlspecialchars($v['brand']); ?></span>
                      <small class="text-secondary ms-2">(<?php echo htmlspecialchars($v['vehicle_type']); ?>)</small>
                    </div>
                    <span class="badge text-bg-light border"><?php echo htmlspecialchars($v['plate_number']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Rincian Status Pembayaran -->
        <div class="col-12">
          <div class="content-card p-4">
            <h2 class="h5 fw-bold mb-3">Rangkuman Pembayaran</h2>
            <div class="row g-2 text-center">
              <div class="col-6">
                <div class="p-2 border rounded bg-light">
                  <span class="small text-secondary d-block">Berhasil (Paid)</span>
                  <span class="fs-5 fw-bold text-success"><?php echo $paymentCounts['paid']; ?></span>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 border rounded bg-light">
                  <span class="small text-secondary d-block">Menunggu (Pending)</span>
                  <span class="fs-5 fw-bold text-warning"><?php echo $paymentCounts['pending']; ?></span>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 border rounded bg-light">
                  <span class="small text-secondary d-block">Gagal (Failed)</span>
                  <span class="fs-5 fw-bold text-danger"><?php echo $paymentCounts['failed']; ?></span>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 border rounded bg-light">
                  <span class="small text-secondary d-block">Kedaluwarsa</span>
                  <span class="fs-5 fw-bold text-secondary"><?php echo $paymentCounts['expired']; ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>