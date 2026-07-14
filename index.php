<?php
session_start();

$pageTitle = 'CarWash Management System';
require __DIR__ . '/config/database.php';

// Fetch active services for the homepage
$serviceStatement = $pdo->prepare('SELECT id, service_name, description, price, duration FROM services WHERE status = :status ORDER BY id DESC LIMIT 6');
$serviceStatement->execute(['status' => 'active']);
$services = $serviceStatement->fetchAll();

$testimonials = [
    [
        'name' => 'Budi Santoso',
        'rating' => 5,
        'review' => 'Pelayanannya sangat cepat dan bersih! Hasil cuci mobil luar dalam kinclong. Booking online lewat web ini juga gampang banget.',
        'vehicle' => 'Toyota Avanza'
    ],
    [
        'name' => 'Siti Aminah',
        'rating' => 5,
        'review' => 'Cuci motor standar cuma 30 menit dan bersih banget. Harganya juga bersahabat. Recommended buat langganan!',
        'vehicle' => 'Honda Vario'
    ],
    [
        'name' => 'Rian Hidayat',
        'rating' => 5,
        'review' => 'Sistem pembayaran otomatis pakai Midtrans sangat memudahkan. Gak perlu bawa uang tunai atau repot transfer manual.',
        'vehicle' => 'Mitsubishi Xpander'
    ]
];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main>
  <!-- Hero Section -->
  <section class="hero-shell d-flex align-items-center">
    <div class="container py-5">
      <div class="row align-items-center g-5 py-5">
        <div class="col-lg-6">
          <span class="eyebrow">CarWash Management System</span>
          <h1 class="display-4 fw-bold mt-3">Kelola booking cuci kendaraan lebih cepat, rapi, dan modern.</h1>
          <p class="lead text-secondary mt-3">
            Sistem booking, pembayaran online Midtrans, dan manajemen layanan cuci kendaraan.
          </p>
          <div class="d-flex gap-3 mt-4 flex-wrap">
            <a class="btn btn-primary btn-lg px-4 rounded-pill" href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Daftar Sekarang</a>
            <a class="btn btn-outline-dark btn-lg px-4 rounded-pill" href="#services-section">Lihat Layanan</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="hero-card p-4 p-md-5">
            <div class="row g-3">
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-label">Booking Aktif</span>
                  <strong>128+</strong>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-label">Tingkat Kepuasan</span>
                  <strong>99.7%</strong>
                </div>
              </div>
              <div class="col-12">
                <div class="feature-card">
                  <h2 class="h5 mb-2">Alur booking sederhana</h2>
                  <p class="mb-0 text-secondary">Pilih kendaraan, layanan, jadwal, lalu selesaikan pembayaran secara online dengan mudah.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Daftar Layanan & Harga Section -->
  <section id="services-section" class="py-5 bg-light border-top border-bottom">
    <div class="container py-4">
      <div class="text-center mb-5">
        <span class="eyebrow">Layanan Kami</span>
        <h2 class="display-6 fw-bold mt-2">Daftar Layanan & Harga</h2>
        <p class="text-secondary mx-auto" style="max-width: 600px;">
          Pilih dari berbagai layanan cuci kendaraan terbaik kami yang disesuaikan dengan kebutuhan Anda.
        </p>
      </div>

      <?php if (empty($services)): ?>
        <div class="alert alert-info text-center">Belum ada layanan aktif.</div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($services as $service): ?>
            <div class="col-md-4">
              <div class="content-card h-100 p-4 transition-card d-flex flex-column">
                <h3 class="h5 fw-bold mb-2"><?php echo htmlspecialchars($service['service_name']); ?></h3>
                <p class="text-secondary mb-4"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                  <span class="fs-4 fw-bold text-teal">Rp <?php echo number_format((float) $service['price'], 0, ',', '.'); ?></span>
                  <span class="badge text-bg-secondary rounded-pill"><?php echo (int) $service['duration']; ?> menit</span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Testimoni Section -->
  <section class="py-5">
    <div class="container py-4">
      <div class="text-center mb-5">
        <span class="eyebrow">Ulasan Pelanggan</span>
        <h2 class="display-6 fw-bold mt-2">Apa Kata Mereka?</h2>
        <p class="text-secondary mx-auto" style="max-width: 600px;">
          Kepuasan pelanggan adalah prioritas utama kami. Berikut testimoni jujur dari pelanggan setia kami.
        </p>
      </div>

      <div class="row g-4">
        <?php foreach ($testimonials as $t): ?>
          <div class="col-md-4">
            <div class="content-card p-4 h-100 d-flex flex-column transition-card">
              <div class="text-warning mb-3">
                <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                  <i class="bi bi-star-fill text-warning"></i>
                <?php endfor; ?>
              </div>
              <p class="text-secondary fst-italic mb-4">"<?php echo htmlspecialchars($t['review']); ?>"</p>
              <div class="mt-auto pt-3 border-top">
                <h4 class="h6 fw-bold mb-0"><?php echo htmlspecialchars($t['name']); ?></h4>
                <small class="text-secondary"><?php echo htmlspecialchars($t['vehicle']); ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Kontak Section -->
  <section class="py-5 bg-dark text-white border-top">
    <div class="container py-4">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <span class="eyebrow text-teal-light">Hubungi Kami</span>
          <h2 class="display-6 fw-bold text-white mt-2 mb-3">Punya pertanyaan seputar layanan?</h2>
          <p class="text-white-50 mb-4">
            Tim support kami siap membantu Anda dengan cepat untuk masalah booking, pembayaran online, atau info kemitraan.
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <a class="btn btn-teal-primary px-4 rounded-pill" href="<?php echo htmlspecialchars(app_url('contact.php')); ?>">Halaman Kontak</a>
            <a class="btn btn-outline-light px-4 rounded-pill" href="mailto:support@carwash.test">Kirim Email</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="stat-card bg-dark-soft border-secondary-teal p-3 rounded">
                <span class="stat-label text-teal-light"><i class="bi bi-telephone-fill me-1"></i> Telepon</span>
                <strong class="text-white">0812-3456-7890</strong>
              </div>
            </div>
            <div class="col-md-6">
              <div class="stat-card bg-dark-soft border-secondary-teal p-3 rounded">
                <span class="stat-label text-teal-light"><i class="bi bi-envelope-fill me-1"></i> Email</span>
                <strong class="text-white">support@carwash.test</strong>
              </div>
            </div>
            <div class="col-12">
              <div class="stat-card bg-dark-soft border-secondary-teal p-3 rounded">
                <span class="stat-label text-teal-light"><i class="bi bi-geo-alt-fill me-1"></i> Alamat Utama</span>
                <p class="mb-0 text-white fw-bold">Jl. Raya Kendaraan No. 45, Jakarta Selatan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>