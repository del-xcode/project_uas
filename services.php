<?php
session_start();
$pageTitle = 'Layanan Kami';

require __DIR__ . '/config/database.php';

$serviceStatement = $pdo->prepare('SELECT service_name, description, price, duration FROM services WHERE status = :status ORDER BY id DESC');
$serviceStatement->execute(['status' => 'active']);
$services = $serviceStatement->fetchAll();

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="text-center mb-5">
    <span class="eyebrow">Daftar Layanan</span>
    <h1 class="display-5 fw-bold mt-2 text-teal">Pilihan Layanan Terbaik Untuk Anda</h1>
    <p class="text-secondary mx-auto" style="max-width: 600px;">
      Kami menyediakan berbagai jenis layanan cuci kendaraan yang dirancang untuk menjaga kebersihan dan kilau kendaraan kesayangan Anda.
    </p>
  </div>

  <?php if (empty($services)): ?>
    <div class="alert alert-info text-center py-4">Belum ada layanan aktif saat ini. Silakan hubungi admin.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($services as $service): ?>
        <div class="col-md-4">
          <div class="content-card h-100 p-4 transition-card d-flex flex-column">
            <div class="mb-3">
              <span class="badge bg-light text-teal border border-teal-light rounded-pill py-2 px-3"><?php echo (int) $service['duration']; ?> Menit</span>
            </div>
            <h2 class="h4 fw-bold mb-2"><?php echo htmlspecialchars($service['service_name']); ?></h2>
            <p class="text-secondary mb-4"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
              <span class="fs-4 fw-bold text-teal">Rp <?php echo number_format((float) $service['price'], 0, ',', '.'); ?></span>
              <a class="btn btn-sm btn-primary rounded-pill px-3 d-flex align-items-center" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>">
                <i class="bi bi-calendar-check me-1"></i>
                <span>Booking</span>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>