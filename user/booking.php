<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();
require __DIR__ . '/../config/database.php';

$pageTitle = 'Booking Cuci Kendaraan';
$pageError = null;
$userId = (int) ($_SESSION['user_id'] ?? 0);

$vehicleStatement = $pdo->prepare('SELECT id, vehicle_type, brand, plate_number FROM vehicles WHERE user_id = :user_id ORDER BY id DESC');
$vehicleStatement->execute(['user_id' => $userId]);
$vehicles = $vehicleStatement->fetchAll();

$serviceStatement = $pdo->query('SELECT id, service_name, description, price, duration FROM services WHERE status = "active" ORDER BY id DESC');
$services = $serviceStatement->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
		$serviceId = (int) ($_POST['service_id'] ?? 0);
		$bookingDate = $_POST['booking_date'] ?? '';
		$bookingTime = $_POST['booking_time'] ?? '';

		if ($vehicleId <= 0 || $serviceId <= 0 || $bookingDate === '' || $bookingTime === '') {
				$pageError = 'Semua field booking wajib diisi.';
		} else {
				$vehicleCheck = $pdo->prepare('SELECT id FROM vehicles WHERE id = :id AND user_id = :user_id LIMIT 1');
				$vehicleCheck->execute([
						'id' => $vehicleId,
						'user_id' => $userId,
				]);

				$serviceCheck = $pdo->prepare('SELECT id, price FROM services WHERE id = :id AND status = "active" LIMIT 1');
				$serviceCheck->execute(['id' => $serviceId]);
				$service = $serviceCheck->fetch();

				if (!$vehicleCheck->fetch()) {
						$pageError = 'Kendaraan tidak valid.';
				} elseif (!$service) {
						$pageError = 'Layanan tidak valid.';
				} else {
						try {
								$pdo->beginTransaction();

								$bookingStatement = $pdo->prepare(
										'INSERT INTO bookings (user_id, vehicle_id, service_id, booking_date, booking_time, status) VALUES (:user_id, :vehicle_id, :service_id, :booking_date, :booking_time, :status)'
								);
								$bookingStatement->execute([
										'user_id' => $userId,
										'vehicle_id' => $vehicleId,
										'service_id' => $serviceId,
										'booking_date' => $bookingDate,
										'booking_time' => $bookingTime,
										'status' => 'pending',
								]);

								$bookingId = (int) $pdo->lastInsertId();
								$transactionId = 'TRX-' . date('YmdHis') . '-' . $bookingId;

								$paymentStatement = $pdo->prepare(
										'INSERT INTO payments (booking_id, transaction_id, payment_method, amount, payment_status) VALUES (:booking_id, :transaction_id, :payment_method, :amount, :payment_status)'
								);
								$paymentStatement->execute([
										'booking_id' => $bookingId,
										'transaction_id' => $transactionId,
										'payment_method' => 'Midtrans',
										'amount' => $service['price'],
										'payment_status' => 'pending',
								]);

								$pdo->commit();

								header('Location: ' . app_url('user/orders.php?created=1'));
								exit;
						} catch (Throwable $exception) {
								if ($pdo->inTransaction()) {
										$pdo->rollBack();
								}

								$pageError = 'Booking gagal disimpan.';
						}
				}
		}
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="row g-4">
		<div class="col-lg-4">
			<div class="content-card p-4 h-100">
				<h1 class="h3 mb-3">Booking Baru</h1>
				<p class="text-secondary">Pilih kendaraan, layanan, dan jadwal booking.</p>
				<?php if ($pageError !== null): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
				<?php endif; ?>
				<?php if (empty($vehicles)): ?>
					<div class="alert alert-warning">Belum ada kendaraan. Tambahkan di halaman profil dulu.</div>
				<?php endif; ?>
				<?php if (empty($services)): ?>
					<div class="alert alert-warning">Belum ada layanan aktif. Tambahkan data layanan dari admin atau seed database.</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="col-lg-8">
			<div class="content-card p-4">
				<form method="post" class="row g-3">
					<div class="col-md-6">
						<label class="form-label" for="vehicle_id">Kendaraan</label>
						<select class="form-select" id="vehicle_id" name="vehicle_id" required <?php echo empty($vehicles) ? 'disabled' : ''; ?>>
							<option value="">Pilih kendaraan</option>
							<?php foreach ($vehicles as $vehicle): ?>
								<option value="<?php echo (int) $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['vehicle_type'] . ' - ' . $vehicle['brand'] . ' (' . $vehicle['plate_number'] . ')'); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="service_id">Layanan</label>
						<select class="form-select" id="service_id" name="service_id" required <?php echo empty($services) ? 'disabled' : ''; ?>>
							<option value="">Pilih layanan</option>
							<?php foreach ($services as $service): ?>
								<option value="<?php echo (int) $service['id']; ?>"><?php echo htmlspecialchars($service['service_name'] . ' - Rp ' . number_format((float) $service['price'], 0, ',', '.')); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="booking_date">Tanggal Booking</label>
						<input class="form-control" type="date" id="booking_date" name="booking_date" required min="<?php echo htmlspecialchars(date('Y-m-d')); ?>">
					</div>
					<div class="col-md-6">
						<label class="form-label" for="booking_time">Jam Booking</label>
						<input class="form-control" type="time" id="booking_time" name="booking_time" required>
					</div>
					<div class="col-12 d-grid d-md-flex justify-content-md-end">
						<button class="btn btn-primary btn-lg" type="submit" <?php echo (empty($vehicles) || empty($services)) ? 'disabled' : ''; ?>>Konfirmasi Booking</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>