<?php
session_start();
$pageTitle = 'Tentang Kami';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="row g-5 align-items-center mb-5">
    <div class="col-lg-6">
      <span class="eyebrow">Mengenal CarWash</span>
      <h1 class="display-5 fw-bold mt-2 mb-4 text-teal">Solusi Modern Perawatan Kendaraan Anda</h1>
      <p class="lead text-secondary">
        CarWash Management System adalah platform booking online yang didesain untuk mempercepat, merapikan, dan memodernisasi layanan cuci kendaraan. 
      </p>
      <p class="text-secondary">
        Kami percaya bahwa waktu Anda sangat berharga. Dengan sistem kami, Anda tidak perlu mengantre lama di lokasi. Cukup lakukan booking dari rumah, bayar secara online dengan aman, dan datang pada waktu yang telah Anda tentukan.
      </p>
    </div>
    <div class="col-lg-6">
      <div class="content-card p-4 p-md-5">
        <h3 class="h4 fw-bold mb-3 text-teal">Visi & Misi</h3>
        <p class="text-secondary mb-3">
          <strong>Visi:</strong> Menjadi penyedia layanan pengelolaan perawatan kendaraan berbasis digital yang paling terpercaya, efisien, dan ramah pelanggan.
        </p>
        <p class="text-secondary mb-0">
          <strong>Misi:</strong> Memberikan kemudahan booking secara realtime, mengintegrasikan sistem pembayaran yang aman dan otomatis, serta mempertahankan standar kebersihan kendaraan terbaik untuk setiap pelanggan.
        </p>
      </div>
    </div>
  </div>

  <div class="text-center mb-5">
    <span class="eyebrow">Keunggulan</span>
    <h2 class="display-6 fw-bold mt-2">Mengapa Memilih Kami?</h2>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="content-card p-4 h-100 transition-card">
        <div class="mb-3 d-inline-block" style="font-size: 2rem;">
          ⏱️
        </div>
        <h3 class="h5 fw-bold mb-2">Hemat Waktu</h3>
        <p class="text-secondary mb-0">Atur jadwal pencucian dari mana saja tanpa perlu mengantre berjam-jam di lokasi cuci.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="content-card p-4 h-100 transition-card">
        <div class="mb-3 d-inline-block" style="font-size: 2rem;">
          💳
        </div>
        <h3 class="h5 fw-bold mb-2">Pembayaran Online</h3>
        <p class="text-secondary mb-0">Integrasi gateway pembayaran Midtrans yang aman dengan dukungan QRIS, Transfer Bank, dan E-Wallet.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="content-card p-4 h-100 transition-card">
        <div class="mb-3 d-inline-block" style="font-size: 2rem;">
          ✨
        </div>
        <h3 class="h5 fw-bold mb-2">Layanan Premium</h3>
        <p class="text-secondary mb-0">Dikerjakan oleh tenaga profesional menggunakan bahan pembersih berkualitas tinggi dan peralatan modern.</p>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>