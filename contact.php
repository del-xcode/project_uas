<?php
session_start();
$pageTitle = 'Kontak';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4 p-md-5">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <span class="eyebrow">Hubungi Kami</span>
        <h1 class="h2 mt-2 mb-3">Butuh bantuan untuk booking atau pembayaran?</h1>
        <p class="text-secondary mb-4">Tim kami siap membantu pertanyaan seputar layanan, jadwal cuci kendaraan, dan status pembayaran.</p>
        <div class="d-flex gap-3 flex-wrap">
          <a class="btn btn-primary" href="<?php echo htmlspecialchars(app_url('user/booking.php')); ?>"><i class="bi bi-calendar-check me-1"></i> Buat Booking</a>
          <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('services.php')); ?>"><i class="bi bi-tags me-1"></i> Lihat Layanan</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="stat-card h-100">
              <span class="stat-label"><i class="bi bi-telephone-fill text-teal me-1"></i> Telepon</span>
              <strong>0812-3456-7890</strong>
            </div>
          </div>
          <div class="col-md-6">
            <div class="stat-card h-100">
              <span class="stat-label"><i class="bi bi-envelope-fill text-teal me-1"></i> Email</span>
              <strong>support@carwash.test</strong>
            </div>
          </div>
          <div class="col-12">
            <div class="feature-card">
              <h2 class="h5 mb-2 d-flex align-items-center"><i class="bi bi-clock-history text-teal me-2"></i> Jam Operasional</h2>
              <p class="mb-0 text-secondary">Senin - Sabtu, 08.00 - 17.00 WIB.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>