<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();
require __DIR__ . '/../config/database.php';

$pageTitle = 'Profil';
$pageError = null;
$pageSuccess = null;
$userId = (int) ($_SESSION['user_id'] ?? 0);

$userStatement = $pdo->prepare('SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1');
$userStatement->execute(['id' => $userId]);
$user = $userStatement->fetch();

$editingVehicleId = (int) ($_GET['edit_vehicle'] ?? 0);
$editingVehicle = null;

if ($editingVehicleId > 0) {
	$vehicleCheckStatement = $pdo->prepare('SELECT id, vehicle_type, brand, plate_number FROM vehicles WHERE id = :id AND user_id = :user_id LIMIT 1');
	$vehicleCheckStatement->execute(['id' => $editingVehicleId, 'user_id' => $userId]);
	$editingVehicle = $vehicleCheckStatement->fetch();
}

$deleteVehicleId = (int) ($_GET['delete_vehicle'] ?? 0);
if ($deleteVehicleId > 0) {
	$deleteStatement = $pdo->prepare('DELETE FROM vehicles WHERE id = :id AND user_id = :user_id');
	$deleteStatement->execute([
		'id' => $deleteVehicleId,
		'user_id' => $userId,
	]);
	header('Location: ' . app_url('user/profile.php'));
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['action'])) {
		if ($_POST['action'] === 'update_user') {
			$name = trim($_POST['name'] ?? '');
			$email = trim($_POST['email'] ?? '');
			$phone = trim($_POST['phone'] ?? '');

			if ($name === '' || $email === '' || $phone === '') {
				$pageError = 'Semua field profil wajib diisi.';
			} else {
				$checkEmail = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
				$checkEmail->execute(['email' => $email, 'id' => $userId]);

				if ($checkEmail->fetch()) {
					$pageError = 'Email sudah digunakan.';
				} else {
					$updateStatement = $pdo->prepare('UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id');
					$updateStatement->execute([
						'name' => $name,
						'email' => $email,
						'phone' => $phone,
						'id' => $userId,
					]);

					$_SESSION['user_name'] = $name;
					$user = compact('id', 'name', 'email', 'phone');
					$user['id'] = $userId;
					$pageSuccess = 'Profil berhasil diperbarui.';
				}
			}
		} elseif ($_POST['action'] === 'add_vehicle' || $_POST['action'] === 'update_vehicle') {
			$vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
			$vehicleType = trim($_POST['vehicle_type'] ?? '');
			$brand = trim($_POST['brand'] ?? '');
			$plateNumber = trim($_POST['plate_number'] ?? '');

			if ($vehicleType === '' || $brand === '' || $plateNumber === '') {
				$pageError = 'Semua field kendaraan wajib diisi.';
			} else {
				if ($vehicleId > 0 && $_POST['action'] === 'update_vehicle') {
					$updateStatement = $pdo->prepare(
						'UPDATE vehicles SET vehicle_type = :vehicle_type, brand = :brand, plate_number = :plate_number WHERE id = :id AND user_id = :user_id'
					);
					$updateStatement->execute([
						'vehicle_type' => $vehicleType,
						'brand' => $brand,
						'plate_number' => $plateNumber,
						'id' => $vehicleId,
						'user_id' => $userId,
					]);

					$editingVehicle = null;
					$pageSuccess = 'Kendaraan berhasil diperbarui.';
				} else {
					$insertStatement = $pdo->prepare(
						'INSERT INTO vehicles (user_id, vehicle_type, brand, plate_number) VALUES (:user_id, :vehicle_type, :brand, :plate_number)'
					);
					$insertStatement->execute([
						'user_id' => $userId,
						'vehicle_type' => $vehicleType,
						'brand' => $brand,
						'plate_number' => $plateNumber,
					]);

					$pageSuccess = 'Kendaraan berhasil ditambahkan.';
				}
			}
		}
	}
}

$vehicleStatement = $pdo->prepare('SELECT id, vehicle_type, brand, plate_number FROM vehicles WHERE user_id = :user_id ORDER BY id DESC');
$vehicleStatement->execute(['user_id' => $userId]);
$vehicles = $vehicleStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="row g-4 mb-4">
		<div class="col-lg-6">
			<div class="content-card p-4">
				<h1 class="h3 mb-3">Data Akun</h1>
				<?php if ($pageError !== null && !isset($_POST['vehicle_id'])): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
				<?php endif; ?>
				<?php if ($pageSuccess !== null && isset($_POST['action']) && $_POST['action'] === 'update_user'): ?>
					<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
				<?php endif; ?>
				<form method="post" class="row g-3">
					<input type="hidden" name="action" value="update_user">
					<div class="col-12">
						<label class="form-label" for="name">Nama Lengkap</label>
						<input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
					</div>
					<div class="col-12">
						<label class="form-label" for="email">Email</label>
						<input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
					</div>
					<div class="col-12">
						<label class="form-label" for="phone">Nomor Telepon</label>
						<input class="form-control" type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
					</div>
					<div class="col-12 d-grid">
						<button class="btn btn-primary" type="submit">Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="row g-4">
		<div class="col-lg-5">
			<div class="content-card p-4 h-100">
				<h1 class="h3 mb-3"><?php echo $editingVehicle ? 'Edit Kendaraan' : 'Tambah Kendaraan'; ?></h1>
				<?php if ($pageError !== null && isset($_POST['vehicle_id'])): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
				<?php endif; ?>
				<?php if ($pageSuccess !== null && (!isset($_POST['action']) || $_POST['action'] !== 'update_user')): ?>
					<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
				<?php endif; ?>
				<form method="post" class="row g-3">
					<input type="hidden" name="action" value="<?php echo $editingVehicle ? 'update_vehicle' : 'add_vehicle'; ?>">
					<input type="hidden" name="vehicle_id" value="<?php echo (int) ($editingVehicle['id'] ?? 0); ?>">
					<div class="col-12">
						<label class="form-label" for="vehicle_type">Jenis Kendaraan</label>
						<select class="form-select" id="vehicle_type" name="vehicle_type" required>
							<option value="">Pilih jenis kendaraan</option>
							<option value="Motor" <?php echo (($editingVehicle['vehicle_type'] ?? '') === 'Motor') ? 'selected' : ''; ?>>Motor</option>
							<option value="Mobil" <?php echo (($editingVehicle['vehicle_type'] ?? '') === 'Mobil') ? 'selected' : ''; ?>>Mobil</option>
							<option value="Lainnya" <?php echo (($editingVehicle['vehicle_type'] ?? '') === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
						</select>
					</div>
					<div class="col-12">
						<label class="form-label" for="brand">Merk / Model</label>
						<input class="form-control" type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($editingVehicle['brand'] ?? ''); ?>" required>
					</div>
					<div class="col-12">
						<label class="form-label" for="plate_number">Nomor Plat</label>
						<input class="form-control" type="text" id="plate_number" name="plate_number" value="<?php echo htmlspecialchars($editingVehicle['plate_number'] ?? ''); ?>" required>
					</div>
					<div class="col-12 d-grid d-md-flex gap-2">
						<button class="btn btn-primary" type="submit"><?php echo $editingVehicle ? 'Update' : 'Simpan'; ?></button>
						<?php if ($editingVehicle): ?>
							<a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars(app_url('user/profile.php')); ?>">Batal</a>
						<?php endif; ?>
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
										<td class="text-end">										<a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars(app_url('user/profile.php?edit_vehicle=' . (int) $vehicle['id'])); ?>">Edit</a>											<a class="btn btn-sm btn-outline-danger" href="<?php echo htmlspecialchars(app_url('user/profile.php?delete_vehicle=' . (int) $vehicle['id'])); ?>" onclick="return confirm('Hapus kendaraan ini?')">Hapus</a>
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