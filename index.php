<?php
session_start();

$pageTitle = 'CarWash Management System';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="hero-shell">
  <section class="hero container py-5">
    <div class="row align-items-center g-5 py-5">
      <div class="col-lg-6">
        <span class="eyebrow">CarWash Management System</span>
        <h1 class="display-4 fw-bold mt-3">Kelola booking cuci kendaraan lebih cepat, rapi, dan modern.</h1>
        <p class="lead text-secondary mt-3">
          Sistem berbasis PHP Native untuk booking, pembayaran online Midtrans, dan manajemen layanan cuci kendaraan.
        </p>
        <div class="d-flex gap-3 mt-4 flex-wrap">
          <a class="btn btn-primary btn-lg" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Daftar Sekarang</a>
          <a class="btn btn-outline-dark btn-lg" href="<?php echo htmlspecialchars(app_url('services.php')); ?>">Lihat Layanan</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-card p-4 p-md-5">
          <div class="row g-3">
            <div class="col-6">
              <div class="stat-card">
                <span class="stat-label">Booking Aktif</span>
                <strong>128</strong>
              </div>
            </div>
            <div class="col-6">
              <div class="stat-card">
                <span class="stat-label">Pembayaran Berhasil</span>
                <strong>97%</strong>
              </div>
            </div>
            <div class="col-12">
              <div class="feature-card">
                <h2 class="h5 mb-2">Alur booking sederhana</h2>
                <p class="mb-0 text-secondary">Pilih kendaraan, layanan, jadwal, lalu selesaikan pembayaran secara online.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>