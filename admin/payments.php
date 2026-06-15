<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');
<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');

require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen Pembayaran';

$paymentStatement = $pdo->query(
		'SELECT
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
		INNER JOIN services s ON s.id = b.service_id
		ORDER BY p.id DESC'
);
$payments = $paymentStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="content-card p-4">
		<h1 class="h3 mb-3">Manajemen Pembayaran</h1>
		<?php if (empty($payments)): ?>
			<div class="alert alert-info mb-0">Belum ada transaksi pembayaran.</div>
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
								<td><span class="badge <?php echo $payment['payment_status'] === 'paid' ? 'text-bg-success' : 'text-bg-warning'; ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></span></td>
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