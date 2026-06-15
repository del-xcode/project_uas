<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();
require __DIR__ . '/../config/database.php';

$pageTitle = 'Profil';
$pageError = null;
$pageSuccess = null;
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$vehicleType = trim($_POST['vehicle_type'] ?? '');
		$brand = trim($_POST['brand'] ?? '');
		$plateNumber = trim($_POST['plate_number'] ?? '');

		if ($vehicleType === '' || $brand === '' || $plateNumber === '') {
				$pageError = 'Semua field kendaraan wajib diisi.';
		} else {
				$statement = $pdo->prepare(
						'INSERT INTO vehicles (user_id, vehicle_type, brand, plate_number) VALUES (:user_id, :vehicle_type, :brand, :plate_number)'
				);
				$statement->execute([
						'user_id' => $userId,
						'vehicle_type' => $vehicleType,
						'brand' => $brand,
						'plate_number' => $plateNumber,
				]);

				$pageSuccess = 'Kendaraan berhasil ditambahkan.';
		}
}

if (isset($_GET['delete_vehicle'])) {
		$vehicleId = (int) $_GET['delete_vehicle'];
		$deleteStatement = $pdo->prepare('DELETE FROM vehicles WHERE id = :id AND user_id = :user_id');
		$deleteStatement->execute([
				'id' => $vehicleId,
				'user_id' => $userId,
		]);

		header('Location: ' . app_url('user/profile.php'));
		exit;
}

$vehicleStatement = $pdo->prepare('SELECT id, vehicle_type, brand, plate_number FROM vehicles WHERE user_id = :user_id ORDER BY id DESC');
$vehicleStatement->execute(['user_id' => $userId]);
$vehicles = $vehicleStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="row g-4">
		<div class="col-lg-5">
			<div class="content-card p-4 h-100">
				<h1 class="h3 mb-3">Profil Kendaraan</h1>
				<?php if ($pageError !== null): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
				<?php endif; ?>
				<?php if ($pageSuccess !== null): ?>
					<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
				<?php endif; ?>
				<form method="post" class="row g-3">
					<div class="col-12">
						<label class="form-label" for="vehicle_type">Jenis Kendaraan</label>
						<select class="form-select" id="vehicle_type" name="vehicle_type" required>
							<option value="">Pilih jenis kendaraan</option>
							<option value="Motor">Motor</option>
							<option value="Mobil">Mobil</option>
							<option value="Lainnya">Lainnya</option>
						</select>
					</div>
					<div class="col-12">
						<label class="form-label" for="brand">Merk / Model</label>
						<input class="form-control" type="text" id="brand" name="brand" required>
					</div>
					<div class="col-12">
						<label class="form-label" for="plate_number">Nomor Plat</label>
						<input class="form-control" type="text" id="plate_number" name="plate_number" required>
					</div>
					<div class="col-12 d-grid">
						<button class="btn btn-primary" type="submit">Simpan Kendaraan</button>
					</div>
				</form>
			</div>
		</div>
		<div class="col-lg-7">
			<div class="content-card p-4 h-100">
				<h2 class="h4 mb-3">Daftar Kendaraan</h2>
				<?php if (empty($vehicles)): ?>
					<div class="alert alert-info mb-0">Belum ada kendaraan. Tambahkan kendaraan terlebih dahulu agar bisa booking.</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table align-middle mb-0">
							<thead>
								<tr>
									<th>Jenis</th>
									<th>Merk / Model</th>
									<th>Plat</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($vehicles as $vehicle): ?>
									<tr>
										<td><?php echo htmlspecialchars($vehicle['vehicle_type']); ?></td>
										<td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
										<td><?php echo htmlspecialchars($vehicle['plate_number']); ?></td>
										<td class="text-end">
											<a class="btn btn-sm btn-outline-danger" href="<?php echo htmlspecialchars(app_url('user/profile.php?delete_vehicle=' . (int) $vehicle['id'])); ?>" onclick="return confirm('Hapus kendaraan ini?')">Hapus</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>