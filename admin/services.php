<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_role('admin');
require __DIR__ . '/../config/database.php';

$pageTitle = 'Manajemen Layanan';
$pageError = null;
$pageSuccess = null;

$editingService = null;
if (isset($_GET['edit_service'])) {
		$editId = (int) $_GET['edit_service'];
		$editStatement = $pdo->prepare('SELECT id, service_name, description, price, duration, status FROM services WHERE id = :id LIMIT 1');
		$editStatement->execute(['id' => $editId]);
		$editingService = $editStatement->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		require_csrf();
		$action = $_POST['action'] ?? 'save';

		if ($action === 'delete') {
				$deleteId = (int) ($_POST['service_id'] ?? 0);
				$deleteStatement = $pdo->prepare('DELETE FROM services WHERE id = :id');
				$deleteStatement->execute(['id' => $deleteId]);
				$pageSuccess = 'Layanan berhasil dihapus.';
		} else {
				$serviceId = (int) ($_POST['service_id'] ?? 0);
				$serviceName = trim($_POST['service_name'] ?? '');
				$description = trim($_POST['description'] ?? '');
				$price = (float) ($_POST['price'] ?? 0);
				$duration = (int) ($_POST['duration'] ?? 0);
				$status = $_POST['status'] ?? 'active';

				if ($serviceName === '' || $price <= 0 || $duration <= 0) {
						$pageError = 'Nama layanan, harga, dan durasi wajib diisi dengan benar.';
				} else {
						if ($serviceId > 0) {
								$updateStatement = $pdo->prepare(
										'UPDATE services SET service_name = :service_name, description = :description, price = :price, duration = :duration, status = :status WHERE id = :id'
								);
								$updateStatement->execute([
										'service_name' => $serviceName,
										'description' => $description,
										'price' => $price,
										'duration' => $duration,
										'status' => $status === 'inactive' ? 'inactive' : 'active',
										'id' => $serviceId,
								]);

								$pageSuccess = 'Layanan berhasil diperbarui.';
								$editingService = null;
						} else {
								$insertStatement = $pdo->prepare(
										'INSERT INTO services (service_name, description, price, duration, status) VALUES (:service_name, :description, :price, :duration, :status)'
								);
								$insertStatement->execute([
										'service_name' => $serviceName,
										'description' => $description,
										'price' => $price,
										'duration' => $duration,
										'status' => $status === 'inactive' ? 'inactive' : 'active',
								]);

								$pageSuccess = 'Layanan berhasil ditambahkan.';
						}
				}
		}
}

$serviceStatement = $pdo->query('SELECT id, service_name, description, price, duration, status FROM services ORDER BY id DESC');
$services = $serviceStatement->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
	<div class="row g-4">
		<div class="col-lg-5">
			<div class="content-card p-4 h-100">
				<h1 class="h3 mb-3 d-flex align-items-center">
					<i class="<?php echo $editingService ? 'bi bi-pencil-square' : 'bi bi-plus-circle'; ?> text-teal me-2"></i>
					<span><?php echo $editingService ? 'Edit Layanan' : 'Tambah Layanan'; ?></span>
				</h1>
				<?php if ($pageError !== null): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
				<?php endif; ?>
				<?php if ($pageSuccess !== null): ?>
					<div class="alert alert-success"><?php echo htmlspecialchars($pageSuccess); ?></div>
				<?php endif; ?>
				<form method="post" class="row g-3">
					<?php echo csrf_input(); ?>
					<input type="hidden" name="service_id" value="<?php echo (int) ($editingService['id'] ?? 0); ?>">
					<div class="col-12">
						<label class="form-label fw-semibold" for="service_name"><i class="bi bi-tag-fill text-teal me-1"></i> Nama Layanan</label>
						<input class="form-control" type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($editingService['service_name'] ?? ''); ?>" required>
					</div>
					<div class="col-12">
						<label class="form-label fw-semibold" for="description"><i class="bi bi-card-text text-teal me-1"></i> Deskripsi</label>
						<textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($editingService['description'] ?? ''); ?></textarea>
					</div>
					<div class="col-md-6">
						<label class="form-label fw-semibold" for="price"><i class="bi bi-cash text-teal me-1"></i> Harga</label>
						<input class="form-control" type="number" id="price" name="price" min="0" step="1" value="<?php echo htmlspecialchars((string) ($editingService['price'] ?? '')); ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label fw-semibold" for="duration"><i class="bi bi-clock-fill text-teal me-1"></i> Durasi (menit)</label>
						<input class="form-control" type="number" id="duration" name="duration" min="1" step="1" value="<?php echo htmlspecialchars((string) ($editingService['duration'] ?? '')); ?>" required>
					</div>
					<div class="col-12">
						<label class="form-label fw-semibold" for="status"><i class="bi bi-toggle-on text-teal me-1"></i> Status</label>
						<select class="form-select" id="status" name="status">
							<option value="active" <?php echo (($editingService['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
							<option value="inactive" <?php echo (($editingService['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
						</select>
					</div>
					<div class="col-12 d-grid d-md-flex gap-2">
						<button class="btn btn-primary" type="submit"><?php echo $editingService ? 'Update' : 'Simpan'; ?></button>
						<?php if ($editingService): ?>
							<a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars(app_url('admin/services.php')); ?>">Batal</a>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
		<div class="col-lg-7">
			<div class="content-card p-4 h-100">
				<h2 class="h4 mb-3 d-flex align-items-center">
					<i class="bi bi-list-task text-teal me-2"></i>
					<span>Daftar Layanan</span>
				</h2>
				<?php if (empty($services)): ?>
					<div class="alert alert-info mb-0">Belum ada data layanan.</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th>Nama</th>
									<th>Harga</th>
									<th>Durasi</th>
									<th>Status</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($services as $service): ?>
									<tr>
										<td>
											<strong><?php echo htmlspecialchars($service['service_name']); ?></strong><br>
											<small class="text-secondary"><?php echo htmlspecialchars($service['description'] ?? ''); ?></small>
										</td>
										<td>Rp <?php echo number_format((float) $service['price'], 0, ',', '.'); ?></td>
										<td><?php echo (int) $service['duration']; ?> menit</td>
										<td><span class="badge <?php echo $service['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo htmlspecialchars($service['status']); ?></span></td>
										<td class="text-end">
											<a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars(app_url('admin/services.php?edit_service=' . (int) $service['id'])); ?>">Edit</a>
											<form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
												<?php echo csrf_input(); ?>
												<input type="hidden" name="action" value="delete">
												<input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
												<button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
											</form>
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