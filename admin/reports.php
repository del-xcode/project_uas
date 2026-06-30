<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Laporan';

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$conditions = [];
$params = [];

if ($startDate !== '') {
    $conditions[] = 'b.booking_date >= :start_date';
    $params['start_date'] = $startDate;
}
if ($endDate !== '') {
    $conditions[] = 'b.booking_date <= :end_date';
    $params['end_date'] = $endDate;
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = ' WHERE ' . implode(' AND ', $conditions);
}

// 1. Summary Query
$summaryQuery = 'SELECT
        COUNT(DISTINCT b.id) AS total_bookings,
        COUNT(DISTINCT p.id) AS total_payments,
        COALESCE(SUM(CASE WHEN p.payment_status = "paid" THEN p.amount ELSE 0 END), 0) AS total_income,
        COUNT(DISTINCT CASE WHEN b.status = "done" THEN b.id END) AS completed_bookings,
        COUNT(DISTINCT CASE WHEN b.status = "pending" THEN b.id END) AS pending_bookings
    FROM bookings b
    LEFT JOIN payments p ON p.booking_id = b.id' . $whereClause;

$summaryStatement = $pdo->prepare($summaryQuery);
$summaryStatement->execute($params);
$summary = $summaryStatement->fetch() ?: [];

// 2. Booking Status Query
$bookingStatusQuery = 'SELECT b.status AS booking_status, COUNT(*) AS total
     FROM bookings b' . $whereClause . '
     GROUP BY b.status
     ORDER BY total DESC';

$statusStatement = $pdo->prepare($bookingStatusQuery);
$statusStatement->execute($params);
$bookingStatuses = $statusStatement->fetchAll();

// 3. Payment Status Query
$paymentStatusQuery = 'SELECT p.payment_status, COUNT(*) AS total, COALESCE(SUM(p.amount), 0) AS amount_total
     FROM payments p
     INNER JOIN bookings b ON b.id = p.booking_id' . $whereClause . '
     GROUP BY p.payment_status
     ORDER BY total DESC';

$paymentStatement = $pdo->prepare($paymentStatusQuery);
$paymentStatement->execute($params);
$paymentStatuses = $paymentStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<style>
@media print {
  body {
    background: #fff !important;
    color: #000 !important;
  }
  .navbar, footer, .d-print-none, .btn, form {
    display: none !important;
  }
  .content-card {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    background: transparent !important;
  }
}
</style>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Laporan</h1>
        <p class="text-secondary mb-0 d-print-none">Ringkasan booking dan pendapatan sistem.</p>
        <?php if ($startDate !== '' || $endDate !== ''): ?>
          <p class="text-teal mb-0 d-none d-print-block fw-bold">
            Periode Laporan: 
            <?php 
              echo ($startDate !== '' ? date('d/m/Y', strtotime($startDate)) : 'Awal');
              echo ' s.d. ';
              echo ($endDate !== '' ? date('d/m/Y', strtotime($endDate)) : 'Akhir');
            ?>
          </p>
        <?php endif; ?>
      </div>
      <a class="btn btn-primary d-print-none" href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Kembali ke Dashboard</a>
    </div>

    <!-- Filter Tanggal & Cetak Form -->
    <form method="get" class="row g-3 mb-4 d-print-none align-items-end">
      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-secondary fw-semibold">Tanggal Mulai</label>
        <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
      </div>
      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-secondary fw-semibold">Tanggal Selesai</label>
        <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
        <?php if ($startDate !== '' || $endDate !== ''): ?>
          <a href="reports.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-dark" onclick="window.print()">Cetak</button>
      </div>
    </form>

    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="stat-card h-100">
          <span class="stat-label">Total Booking</span>
          <strong><?php echo (int) ($summary['total_bookings'] ?? 0); ?></strong>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-card h-100">
          <span class="stat-label">Total Pembayaran</span>
          <strong><?php echo (int) ($summary['total_payments'] ?? 0); ?></strong>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-card h-100">
          <span class="stat-label">Pendapatan Paid</span>
          <strong>Rp <?php echo number_format((float) ($summary['total_income'] ?? 0), 0, ',', '.'); ?></strong>
        </div>
      </div>
      <div class="col-md-3 col-6">
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
                      <td class="text-capitalize"><?php echo htmlspecialchars($item['booking_status']); ?></td>
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
                      <td class="text-capitalize"><?php echo htmlspecialchars($item['payment_status']); ?></td>
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