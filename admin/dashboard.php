<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard Admin';

// 1. Total User
$totalUsersStatement = $pdo->query('SELECT COUNT(*) AS total FROM users');
$totalUsers = (int) ($totalUsersStatement->fetch()['total'] ?? 0);

// 2. Total Booking
$totalBookingsStatement = $pdo->query('SELECT COUNT(*) AS total FROM bookings');
$totalBookings = (int) ($totalBookingsStatement->fetch()['total'] ?? 0);

// 3. Booking Hari Ini
$bookingsTodayStatement = $pdo->query('SELECT COUNT(*) AS total FROM bookings WHERE booking_date = CURRENT_DATE');
$bookingsToday = (int) ($bookingsTodayStatement->fetch()['total'] ?? 0);

// 4. Pendapatan Bulanan
$monthlyIncomeStatement = $pdo->query(
    'SELECT COALESCE(SUM(amount), 0) AS total 
     FROM payments 
     WHERE payment_status = "paid" 
       AND MONTH(created_at) = MONTH(CURRENT_DATE) 
       AND YEAR(created_at) = YEAR(CURRENT_DATE)'
);
$monthlyIncome = (float) ($monthlyIncomeStatement->fetch()['total'] ?? 0);

// 5. Status Pembayaran (breakdown)
$statusCounts = [
    'pending' => 0,
    'paid' => 0,
    'failed' => 0,
    'expired' => 0,
];
$paymentStatusStatement = $pdo->query('SELECT payment_status, COUNT(*) AS count FROM payments GROUP BY payment_status');
foreach ($paymentStatusStatement->fetchAll() as $row) {
    if (isset($statusCounts[$row['payment_status']])) {
        $statusCounts[$row['payment_status']] = (int) $row['count'];
    }
}
$totalPayments = array_sum($statusCounts);

// 6. Grafik Transaksi (7 Hari Terakhir)
$chartStatement = $pdo->query(
    'SELECT DATE(created_at) as trx_date, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount
     FROM payments
     WHERE payment_status = "paid" AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)
     GROUP BY DATE(created_at)
     ORDER BY trx_date ASC'
);
$chartData = $chartStatement->fetchAll();

$daysData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daysData[$date] = [
        'day_name' => date('d M', strtotime("-$i days")),
        'count' => 0,
        'amount' => 0.0
    ];
}

foreach ($chartData as $row) {
    if (isset($daysData[$row['trx_date']])) {
        $daysData[$row['trx_date']]['count'] = (int) $row['count'];
        $daysData[$row['trx_date']]['amount'] = (float) $row['total_amount'];
    }
}

$maxAmount = 0.0;
foreach ($daysData as $day) {
    if ($day['amount'] > $maxAmount) {
        $maxAmount = $day['amount'];
    }
}
if ($maxAmount === 0.0) {
    $maxAmount = 10000.0; 
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h1 class="h3 mb-1">Dashboard Admin</h1>
        <p class="text-secondary mb-0">Ringkasan operasional sistem CarWash.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('admin/orders.php')); ?>">Kelola Booking</a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('admin/services.php')); ?>">Kelola Layanan</a>
        <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars(app_url('admin/reports.php')); ?>">Laporan</a>
      </div>
    </div>
  </div>

  <!-- Row 1: Quick Stats (4 Widgets) -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-people-fill text-teal fs-3 mb-2 d-block"></i>
          <span class="stat-label">Total User</span>
        </div>
        <strong class="text-teal"><?php echo $totalUsers; ?></strong>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-calendar3 text-teal fs-3 mb-2 d-block"></i>
          <span class="stat-label">Total Booking</span>
        </div>
        <strong class="text-teal"><?php echo $totalBookings; ?></strong>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-calendar-check-fill text-warning fs-3 mb-2 d-block"></i>
          <span class="stat-label">Booking Hari Ini</span>
        </div>
        <strong class="text-warning"><?php echo $bookingsToday; ?></strong>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card h-100 transition-card d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-cash-coin text-success fs-3 mb-2 d-block"></i>
          <span class="stat-label">Pendapatan Bulanan</span>
        </div>
        <strong class="text-success">Rp <?php echo number_format($monthlyIncome, 0, ',', '.'); ?></strong>
      </div>
    </div>
  </div>

  <!-- Row 2: Status Pembayaran & Grafik Transaksi -->
  <div class="row g-4">
    <!-- Status Pembayaran breakdown -->
    <div class="col-lg-5">
      <div class="content-card p-4 h-100">
        <h2 class="h5 mb-4 fw-bold">Status Pembayaran</h2>
        <div class="d-flex flex-column gap-3">
          <?php
          $statuses = [
              'paid' => ['label' => 'Paid (Berhasil)', 'color' => 'bg-success'],
              'pending' => ['label' => 'Pending', 'color' => 'bg-warning'],
              'failed' => ['label' => 'Failed (Gagal)', 'color' => 'bg-danger'],
              'expired' => ['label' => 'Expired (Kedaluwarsa)', 'color' => 'bg-secondary'],
          ];

          foreach ($statuses as $key => $meta):
              $count = $statusCounts[$key];
              $percentage = $totalPayments > 0 ? ($count / $totalPayments) * 100 : 0;
          ?>
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="fw-medium text-capitalize"><?php echo $meta['label']; ?></span>
                <span class="text-secondary small"><?php echo $count; ?> transaksi (<?php echo round($percentage, 1); ?>%)</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div class="progress-bar <?php echo $meta['color']; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Grafik Transaksi (SVG/CSS bar chart) -->
    <div class="col-lg-7">
      <div class="content-card p-4 h-100">
        <h2 class="h5 mb-4 fw-bold">Grafik Transaksi (Pendapatan 7 Hari Terakhir)</h2>
        <div class="d-flex justify-content-between align-items-end h-75 pt-3" style="min-height: 200px;">
          <?php foreach ($daysData as $date => $day): 
              $height = ($day['amount'] / $maxAmount) * 100;
              // Ensure some visual feedback even if it is 0, say 4px
              $heightPercent = $day['amount'] > 0 ? "height: {$height}%" : "height: 8px";
          ?>
            <div class="d-flex flex-column align-items-center flex-grow-1 mx-1" style="height: 100%; justify-content: flex-end;">
              <div class="small text-secondary mb-1 d-none d-md-block" style="font-size: 0.75rem;">
                Rp<?php echo number_format($day['amount'] / 1000, 0, ',', '.'); ?>k
              </div>
              <div class="w-100 rounded-top transition-card <?php echo $day['amount'] > 0 ? 'bg-primary' : 'bg-secondary-subtle'; ?>" 
                   style="<?php echo $heightPercent; ?>; min-width: 24px;" 
                   title="<?php echo htmlspecialchars($day['day_name'] . ': Rp ' . number_format($day['amount'], 0, ',', '.')); ?>">
              </div>
              <span class="mt-2 text-secondary fw-semibold" style="font-size: 0.75rem;">
                <?php echo htmlspecialchars($day['day_name']); ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>