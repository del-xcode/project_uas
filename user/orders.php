<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();
require __DIR__ . '/../config/database.php';

$pageTitle = 'Riwayat Booking';
$userId = (int) ($_SESSION['user_id'] ?? 0);

$bookingStatement = $pdo->prepare(
		'SELECT
				b.id,
				b.booking_date,
				b.booking_time,
				b.status AS booking_status,
				s.service_name,
				s.price,
				v.vehicle_type,
				v.brand,
				v.plate_number,
				p.transaction_id,
				p.payment_method,
				p.payment_status
		FROM bookings b
		INNER JOIN services s ON s.id = b.service_id
		INNER JOIN vehicles v ON v.id = b.vehicle_id
		LEFT JOIN payments p ON p.booking_id = b.id
		WHERE b.user_id = :user_id
		ORDER BY b.id DESC'
);
$bookingStatement->execute(['user_id' => $userId]);
$bookings = $bookingStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="content-card p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h1 class="h3 mb-1">Riwayat Booking</h1>
				<p class="text-secondary mb-0">Semua booking dan status pembayaran Anda.</p>
			</div>
			<a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>">Buat Booking Baru</a>
		</div>

		<?php if (isset($_GET['created'])): ?>
			<div class="alert alert-success">Booking berhasil dibuat dan status pembayaran masih pending.</div>
		<?php endif; ?>

		<?php if (empty($bookings)): ?>
			<div class="alert alert-info mb-0">Belum ada booking.</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>#</th>
							<th>Tanggal / Jam</th>
							<th>Kendaraan</th>
							<th>Layanan</th>
							<th>Tagihan</th>
							<th>Status Booking</th>
							<th>Status Pembayaran</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bookings as $booking): ?>
							<tr>
								<td><?php echo (int) $booking['id']; ?></td>
								<td><?php echo htmlspecialchars($booking['booking_date'] . ' / ' . $booking['booking_time']); ?></td>
								<td>
									<?php echo htmlspecialchars($booking['vehicle_type'] . ' - ' . $booking['brand']); ?><br>
									<small class="text-secondary"><?php echo htmlspecialchars($booking['plate_number']); ?></small>
								</td>
								<td><?php echo htmlspecialchars($booking['service_name']); ?></td>
								<td>Rp <?php echo number_format((float) $booking['price'], 0, ',', '.'); ?></td>
								<td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($booking['booking_status']); ?></span></td>
								<td><span class="badge text-bg-warning"><?php echo htmlspecialchars($booking['payment_status'] ?? 'pending'); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>