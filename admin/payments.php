<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen Pembayaran';

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$query = 'SELECT
            p.id,
            p.transaction_id,
            p.payment_method,
            p.amount,
            p.payment_status,
            p.created_at,
            b.id AS booking_id,
            b.booking_date,
            b.booking_time,
            b.status AS booking_status,
            u.name AS user_name,
            u.email AS user_email,
            s.service_name
        FROM payments p
        INNER JOIN bookings b ON b.id = p.booking_id
        INNER JOIN users u ON u.id = b.user_id
        INNER JOIN services s ON s.id = b.service_id';

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(p.transaction_id LIKE :search OR u.name LIKE :search OR u.email LIKE :search)';
    $params['search'] = "%{$search}%";
}

if ($status !== '') {
    $conditions[] = 'p.payment_status = :status';
    $params['status'] = $status;
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY p.id DESC';

$paymentStatement = $pdo->prepare($query);
$paymentStatement->execute($params);
$payments = $paymentStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="content-card p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h1 class="h3 mb-1 d-flex align-items-center">
					<i class="bi bi-credit-card text-teal me-2"></i>
					<span>Manajemen Pembayaran</span>
				</h1>
				<p class="text-secondary mb-0">Kelola dan pantau seluruh transaksi pembayaran masuk.</p>
			</div>
		</div>

		<!-- Search and Filter Form -->
		<form method="get" class="row g-2 mb-4">
			<div class="col-md-5">
				<input type="text" class="form-control" name="search" placeholder="Cari Order ID, Nama, atau Email..." value="<?php echo htmlspecialchars($search); ?>">
			</div>
			<div class="col-md-3">
				<select class="form-select" name="status">
					<option value="">Semua Status</option>
					<option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
					<option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
					<option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
					<option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expired</option>
				</select>
			</div>
			<div class="col-md-2 d-grid">
				<button type="submit" class="btn btn-primary">Filter</button>
			</div>
			<?php if ($search !== '' || $status !== ''): ?>
				<div class="col-md-2 d-grid">
					<a href="payments.php" class="btn btn-outline-secondary">Reset</a>
				</div>
			<?php endif; ?>
		</form>

		<?php if (empty($payments)): ?>
			<div class="alert alert-info mb-0">Tidak ada transaksi pembayaran ditemukan.</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>#</th>
							<th>User</th>
							<th>Booking</th>
							<th>Transaksi</th>
							<th>Metode</th>
							<th>Nominal</th>
							<th>Status</th>
							<th>Dibuat</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($payments as $payment): ?>
							<tr>
								<td><?php echo (int) $payment['id']; ?></td>
								<td>
									<strong><?php echo htmlspecialchars($payment['user_name']); ?></strong><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['user_email']); ?></small>
								</td>
								<td>
									#<?php echo (int) $payment['booking_id']; ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['booking_date'] . ' / ' . $payment['booking_time']); ?></small><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['service_name']); ?></small>
								</td>
								<td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
								<td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
								<td>Rp <?php echo number_format((float) $payment['amount'], 0, ',', '.'); ?></td>
								<td>
									<?php
									$paymentBadgeColor = 'text-bg-warning';
									if ($payment['payment_status'] === 'paid') {
											$paymentBadgeColor = 'text-bg-success';
									} elseif (in_array($payment['payment_status'], ['failed', 'expired'], true)) {
											$paymentBadgeColor = 'text-bg-danger';
									}
									?>
									<span class="badge <?php echo $paymentBadgeColor; ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></span>
								</td>
								<td><?php echo htmlspecialchars($payment['created_at']); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>