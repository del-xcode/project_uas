<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Laporan';

$summaryStatement = $pdo->query(
    'SELECT
        COUNT(DISTINCT b.id) AS total_bookings,
        COUNT(DISTINCT p.id) AS total_payments,
        COALESCE(SUM(CASE WHEN p.payment_status = "paid" THEN p.amount ELSE 0 END), 0) AS total_income,
        COUNT(DISTINCT CASE WHEN b.status = "done" THEN b.id END) AS completed_bookings,
        COUNT(DISTINCT CASE WHEN b.status = "pending" THEN b.id END) AS pending_bookings
    FROM bookings b
    LEFT JOIN payments p ON p.booking_id = b.id'
);
$summary = $summaryStatement->fetch() ?: [];

$statusStatement = $pdo->query(
    'SELECT b.status AS booking_status, COUNT(*) AS total
     FROM bookings b
     GROUP BY b.status
     ORDER BY total DESC'
);
$bookingStatuses = $statusStatement->fetchAll();

$paymentStatement = $pdo->query(
    'SELECT p.payment_status, COUNT(*) AS total, COALESCE(SUM(p.amount), 0) AS amount_total
     FROM payments p
     GROUP BY p.payment_status
     ORDER BY total DESC'
);
$paymentStatuses = $paymentStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Laporan</h1>
        <p class="text-secondary mb-0">Ringkasan booking dan pendapatan sistem.</p>
      </div>
      <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Kembali ke Dashboard</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Total Booking</span>
          <strong><?php echo (int) ($summary['total_bookings'] ?? 0); ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Total Pembayaran</span>
          <strong><?php echo (int) ($summary['total_payments'] ?? 0); ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Pendapatan Paid</span>
          <strong>Rp <?php echo number_format((float) ($summary['total_income'] ?? 0), 0, ',', '.'); ?></strong>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card h-100">
          <span class="stat-label">Booking Selesai</span>
          <strong><?php echo (int) ($summary['completed_bookings'] ?? 0); ?></strong>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="content-card p-4 h-100">
          <h2 class="h5 mb-3">Status Booking</h2>
          <?php if (empty($bookingStatuses)): ?>
            <div class="alert alert-info mb-0">Belum ada data booking.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($bookingStatuses as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['booking_status']); ?></td>
                      <td><?php echo (int) $item['total']; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="content-card p-4 h-100">
          <h2 class="h5 mb-3">Status Pembayaran</h2>
          <?php if (empty($paymentStatuses)): ?>
            <div class="alert alert-info mb-0">Belum ada data pembayaran.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Nominal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paymentStatuses as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['payment_status']); ?></td>
                      <td><?php echo (int) $item['total']; ?></td>
                      <td>Rp <?php echo number_format((float) $item['amount_total'], 0, ',', '.'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>