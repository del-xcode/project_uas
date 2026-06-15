<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');
require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen Booking';
$pageError = null;
$pageSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$bookingId = (int) ($_POST['booking_id'] ?? 0);
		$bookingStatus = $_POST['booking_status'] ?? 'pending';
		$paymentStatus = $_POST['payment_status'] ?? 'pending';

		if ($bookingId <= 0) {
				$pageError = 'Booking tidak valid.';
		} else {
				try {
						$pdo->beginTransaction();

						$updateBooking = $pdo->prepare('UPDATE bookings SET status = :status WHERE id = :id');
						$updateBooking->execute([
								'status' => in_array($bookingStatus, ['pending', 'process', 'done', 'cancelled'], true) ? $bookingStatus : 'pending',
								'id' => $bookingId,
						]);

						$updatePayment = $pdo->prepare('UPDATE payments SET payment_status = :payment_status WHERE booking_id = :booking_id');
						$updatePayment->execute([
								'payment_status' => in_array($paymentStatus, ['pending', 'paid', 'failed', 'expired'], true) ? $paymentStatus : 'pending',
								'booking_id' => $bookingId,
						]);

						$pdo->commit();
						$pageSuccess = 'Status booking berhasil diperbarui.';
				} catch (Throwable $exception) {
						if ($pdo->inTransaction()) {
								$pdo->rollBack();
						}

						$pageError = 'Gagal memperbarui status booking.';
				}
		}
}

$bookingStatement = $pdo->query(
		'SELECT
				b.id,
				b.booking_date,
				b.booking_time,
				b.status AS booking_status,
				u.name AS user_name,
				u.email AS user_email,
				s.service_name,
				s.price,
				v.vehicle_type,
				v.brand,
				v.plate_number,
				p.payment_status,
				p.payment_method,
				p.transaction_id
		FROM bookings b
		INNER JOIN users u ON u.id = b.user_id
		INNER JOIN services s ON s.id = b.service_id
		INNER JOIN vehicles v ON v.id = b.vehicle_id
		LEFT JOIN payments p ON p.booking_id = b.id
		ORDER BY b.id DESC'
);
$bookings = $bookingStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="content-card p-4">
		<h1 class="h3 mb-3">Manajemen Booking</h1>
		<?php if ($pageError !== null): ?>
			<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
		<?php endif; ?>
		<?php if ($pageSuccess !== null): ?>
			<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
		<?php endif; ?>

		<?php if (empty($bookings)): ?>
			<div class="alert alert-info mb-0">Belum ada booking.</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>#</th>
							<th>User</th>
							<th>Tanggal / Jam</th>
							<th>Kendaraan</th>
							<th>Layanan</th>
							<th>Bayar</th>
							<th>Status Booking</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bookings as $booking): ?>
							<tr>
								<td><?php echo (int) $booking['id']; ?></td>
								<td>
									<strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
									<small class="text-secondary"><?php echo htmlspecialchars($booking['user_email']); ?></small>
								</td>
								<td><?php echo htmlspecialchars($booking['booking_date'] . ' / ' . $booking['booking_time']); ?></td>
								<td>
									<?php echo htmlspecialchars($booking['vehicle_type'] . ' - ' . $booking['brand']); ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($booking['plate_number']); ?></small>
								</td>
								<td>
									<?php echo htmlspecialchars($booking['service_name']); ?><br>
									<small class="text-secondary">Rp <?php echo number_format((float) $booking['price'], 0, ',', '.'); ?></small>
								</td>
								<td>
									<span class="badge text-bg-warning"><?php echo htmlspecialchars($booking['payment_status'] ?? 'pending'); ?></span><br>
									<small class="text-secondary"><?php echo htmlspecialchars($booking['payment_method'] ?? '-'); ?></small>
								</td>
								<td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($booking['booking_status']); ?></span></td>
								<td>
									<form method="post" class="d-grid gap-2">
										<input type="hidden" name="booking_id" value="<?php echo (int) $booking['id']; ?>">
										<select class="form-select form-select-sm" name="booking_status">
											<option value="pending" <?php echo $booking['booking_status'] === 'pending' ? 'selected' : ''; ?>>pending</option>
											<option value="process" <?php echo $booking['booking_status'] === 'process' ? 'selected' : ''; ?>>process</option>
											<option value="done" <?php echo $booking['booking_status'] === 'done' ? 'selected' : ''; ?>>done</option>
											<option value="cancelled" <?php echo $booking['booking_status'] === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
										</select>
										<select class="form-select form-select-sm" name="payment_status">
											<option value="pending" <?php echo (($booking['payment_status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>pending</option>
											<option value="paid" <?php echo (($booking['payment_status'] ?? '') === 'paid') ? 'selected' : ''; ?>>paid</option>
											<option value="failed" <?php echo (($booking['payment_status'] ?? '') === 'failed') ? 'selected' : ''; ?>>failed</option>
											<option value="expired" <?php echo (($booking['payment_status'] ?? '') === 'expired') ? 'selected' : ''; ?>>expired</option>
										</select>
										<button class="btn btn-sm btn-primary" type="submit">Update</button>
									</form>
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