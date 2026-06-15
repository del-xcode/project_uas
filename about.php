<?php
session_start();
$pageTitle = 'Tentang Kami';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card">
    <h1 class="h2">Tentang Kami</h1>
    <p class="text-secondary mb-0">Platform ini dibangun untuk membantu operasional jasa cuci kendaraan dengan alur booking dan pembayaran yang terpusat.</p>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>