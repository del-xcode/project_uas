<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();

require __DIR__ . '/../config/database.php';

$pageTitle = 'Pembayaran';
$pageError = null;
$pageSuccess = null;
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
		require_csrf();
		$bookingId = (int) ($_POST['booking_id'] ?? 0);

		if ($bookingId <= 0) {
				$pageError = 'Booking tidak valid.';
		} else {
				$bookingStatement = $pdo->prepare(
						'SELECT b.id, b.service_id, b.user_id, s.price
						 FROM bookings b
						 INNER JOIN services s ON s.id = b.service_id
						 WHERE b.id = :booking_id AND b.user_id = :user_id
						 LIMIT 1'
				);
				$bookingStatement->execute([
						'booking_id' => $bookingId,
						'user_id' => $userId,
				]);
				$booking = $bookingStatement->fetch();

				if (!$booking) {
						$pageError = 'Booking tidak ditemukan.';
				} else {
						$existingPaymentStatement = $pdo->prepare('SELECT id, payment_status FROM payments WHERE booking_id = :booking_id LIMIT 1');
						$existingPaymentStatement->execute(['booking_id' => $bookingId]);
						$existingPayment = $existingPaymentStatement->fetch();

						$transactionId = 'TRX-' . date('YmdHis') . '-' . $bookingId;

						if ($existingPayment) {
								$updatePayment = $pdo->prepare(
										'UPDATE payments SET transaction_id = :transaction_id, payment_method = :payment_method, amount = :amount, payment_status = :payment_status WHERE booking_id = :booking_id'
								);
								$updatePayment->execute([
										'transaction_id' => $transactionId,
										'payment_method' => 'Midtrans',
										'amount' => $booking['price'],
										'payment_status' => 'pending',
										'booking_id' => $bookingId,
								]);
						} else {
								$insertPayment = $pdo->prepare(
										'INSERT INTO payments (booking_id, transaction_id, payment_method, amount, payment_status) VALUES (:booking_id, :transaction_id, :payment_method, :amount, :payment_status)'
								);
								$insertPayment->execute([
										'booking_id' => $bookingId,
										'transaction_id' => $transactionId,
										'payment_method' => 'Midtrans',
										'amount' => $booking['price'],
										'payment_status' => 'pending',
								]);
						}

						$pageSuccess = 'Invoice pembayaran berhasil dibuat.';
				}
		}
}

$paymentStatement = $pdo->prepare(
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
				s.service_name,
				v.vehicle_type,
				v.brand,
				v.plate_number
		FROM payments p
		INNER JOIN bookings b ON b.id = p.booking_id
		INNER JOIN services s ON s.id = b.service_id
		INNER JOIN vehicles v ON v.id = b.vehicle_id
		WHERE b.user_id = :user_id
		ORDER BY p.id DESC'
);
$paymentStatement->execute(['user_id' => $userId]);
$payments = $paymentStatement->fetchAll();

$pendingBookingStatement = $pdo->prepare(
		'SELECT
				b.id,
				b.booking_date,
				b.booking_time,
				b.status,
				s.service_name,
				s.price,
				v.vehicle_type,
				v.brand,
				v.plate_number
		FROM bookings b
		INNER JOIN services s ON s.id = b.service_id
		INNER JOIN vehicles v ON v.id = b.vehicle_id
		LEFT JOIN payments p ON p.booking_id = b.id
		WHERE b.user_id = :user_id AND p.id IS NULL
		ORDER BY b.id DESC'
);
$pendingBookingStatement->execute(['user_id' => $userId]);
$pendingBookings = $pendingBookingStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="content-card p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h1 class="h3 mb-1 d-flex align-items-center">
					<i class="bi bi-wallet2 text-teal me-2"></i>
					<span>Pembayaran</span>
				</h1>
				<p class="text-secondary mb-0">Lihat invoice dan status pembayaran booking Anda.</p>
			</div>
			<a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>">Buat Booking Baru</a>
		</div>

		<?php if ($pageError !== null): ?>
			<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
		<?php endif; ?>

		<?php if ($pageSuccess !== null): ?>
			<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
		<?php endif; ?>

		<?php if (isset($_GET['simulated'])): ?>
			<div class="alert alert-success border-secondary-teal">
				<strong><i class="bi bi-check-circle-fill text-success me-1"></i> Simulasi Pembayaran Berhasil!</strong> 
				Status pembayaran diperbarui menjadi <strong>Paid</strong> dan status booking diperbarui menjadi <strong>Process</strong>.
			</div>
		<?php endif; ?>

		<?php if (!empty($pendingBookings)): ?>
			<div class="mb-4">
				<h2 class="h5 mb-3">Booking Menunggu Invoice</h2>
				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr>
								<th>Booking</th>
								<th>Kendaraan</th>
								<th>Layanan</th>
								<th>Nominal</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($pendingBookings as $booking): ?>
								<tr>
									<td>
										#<?php echo (int) $booking['id']; ?><br>
										<small class="text-secondary"><?php echo htmlspecialchars($booking['booking_date'] . ' / ' . $booking['booking_time']); ?></small>
									</td>
									<td>
										<?php echo htmlspecialchars($booking['vehicle_type'] . ' - ' . $booking['brand']); ?><br>
										<small class="text-secondary"><?php echo htmlspecialchars($booking['plate_number']); ?></small>
									</td>
									<td><?php echo htmlspecialchars($booking['service_name']); ?></td>
									<td>Rp <?php echo number_format((float) $booking['price'], 0, ',', '.'); ?></td>
									<td>
										<form method="post" class="d-inline">
											<?php echo csrf_input(); ?>
											<input type="hidden" name="booking_id" value="<?php echo (int) $booking['id']; ?>">
											<button class="btn btn-sm btn-primary" type="submit" name="create_payment" value="1">Buat Invoice</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>

		<?php if (empty($payments)): ?>
			<div class="alert alert-info mb-0">Belum ada data pembayaran.</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>#</th>
							<th>Booking</th>
							<th>Kendaraan</th>
							<th>Transaksi</th>
							<th>Nominal</th>
							<th>Status</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($payments as $payment): ?>
							<tr>
								<td><?php echo (int) $payment['id']; ?></td>
								<td>
									#<?php echo (int) $payment['booking_id']; ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['service_name']); ?></small><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['booking_date'] . ' / ' . $payment['booking_time']); ?></small>
								</td>
								<td>
									<?php echo htmlspecialchars($payment['vehicle_type'] . ' - ' . $payment['brand']); ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['plate_number']); ?></small>
								</td>
								<td>
									<?php echo htmlspecialchars($payment['transaction_id']); ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['payment_method']); ?></small>
								</td>
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
									<span class="badge <?php echo $paymentBadgeColor; ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></span><br>
									<small class="text-secondary"><?php echo htmlspecialchars($payment['created_at']); ?></small>
								</td>
								<td>
									<?php if ($payment['payment_status'] !== 'paid'): ?>
										<a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars(app_url('user/payment_checkout.php?payment_id=' . (int) $payment['id'])); ?>">Bayar Sekarang</a>
									<?php else: ?>
										<span class="text-secondary">Selesai</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>