<?php
session_start();
$pageTitle = 'Layanan';

require __DIR__ . '/config/database.php';

$serviceStatement = $pdo->prepare('SELECT service_name, description, price, duration FROM services WHERE status = :status ORDER BY id DESC');
$serviceStatement->execute(['status' => 'active']);
$services = $serviceStatement->fetchAll();

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <h1 class="h2 mb-4">Layanan</h1>
  <?php if (empty($services)): ?>
    <div class="alert alert-info">Belum ada layanan aktif.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($services as $service): ?>
        <div class="col-md-4">
          <div class="content-card h-100 p-4">
            <h2 class="h5"><?php echo htmlspecialchars($service['service_name']); ?></h2>
            <p class="text-secondary mb-3"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <strong>Rp <?php echo number_format((float) $service['price'], 0, ',', '.'); ?></strong>
              <span class="text-secondary"><?php echo (int) $service['duration']; ?> menit</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>