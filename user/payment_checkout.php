<?php
session_start();
require __DIR__ . '/../includes/auth.php';
require_login();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/midtrans.php';

$pageTitle = 'Checkout Pembayaran';
$pageError = null;
$snapToken = null;
$snapUrl = null;

$userId = (int) ($_SESSION['user_id'] ?? 0);
$paymentId = (int) ($_GET['payment_id'] ?? 0);

$paymentStatement = $pdo->prepare(
    'SELECT
        p.id,
        p.transaction_id,
        p.payment_method,
        p.amount,
        p.payment_status,
        p.created_at,
        b.id AS booking_id,
        b.booking_date,
        b.booking_time,
        s.service_name,
        v.vehicle_type,
        v.brand,
        v.plate_number,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone
    FROM payments p
    INNER JOIN bookings b ON b.id = p.booking_id
    INNER JOIN services s ON s.id = b.service_id
    INNER JOIN vehicles v ON v.id = b.vehicle_id
    INNER JOIN users u ON u.id = b.user_id
    WHERE p.id = :payment_id AND b.user_id = :user_id
    LIMIT 1'
);
$paymentStatement->execute([
    'payment_id' => $paymentId,
    'user_id' => $userId,
]);
$payment = $paymentStatement->fetch();

if (!$payment) {
    $pageError = 'Pembayaran tidak ditemukan.';
} elseif (midtrans_is_configured()) {
    $payload = [
        'transaction_details' => [
            'order_id' => $payment['transaction_id'],
            'gross_amount' => (int) $payment['amount'],
        ],
        'customer_details' => [
            'first_name' => $payment['user_name'],
            'email' => $payment['user_email'],
            'phone' => $payment['user_phone'],
        ],
        'item_details' => [[
            'id' => 'booking-' . $payment['booking_id'],
            'price' => (int) $payment['amount'],
            'quantity' => 1,
            'name' => $payment['service_name'],
        ]],
        'callbacks' => [
            'finish' => app_url('user/payment.php'),
        ],
    ];

    try {
        $snapResponse = midtrans_create_snap_token($payload);
        $snapToken = $snapResponse['token'] ?? null;
        $snapUrl = $snapResponse['redirect_url'] ?? null;
    } catch (Throwable $exception) {
        $pageError = $exception->getMessage();
    }
} else {
    $pageError = 'Konfigurasi Midtrans belum diisi. Set server key dan client key terlebih dahulu.';
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-5">
  <div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Checkout Pembayaran</h1>
        <p class="text-secondary mb-0">Siapkan pembayaran untuk booking yang dipilih.</p>
      </div>
      <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars(app_url('user/payment.php')); ?>">Kembali</a>
    </div>

    <?php if ($pageError !== null): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="stat-card h-100">
            <span class="stat-label">Order ID</span>
            <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong>
            <div class="mt-3 text-secondary">
              <div><?php echo htmlspecialchars($payment['service_name']); ?></div>
              <div><?php echo htmlspecialchars($payment['vehicle_type'] . ' - ' . $payment['brand']); ?></div>
              <div><?php echo htmlspecialchars($payment['plate_number']); ?></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="content-card p-4 h-100">
            <h2 class="h5 mb-3">Langkah Pembayaran</h2>
            <ol class="text-secondary mb-0">
              <li>Klik tombol bayar untuk membuka Snap Midtrans.</li>
              <li>Pilih metode pembayaran yang tersedia.</li>
              <li>Status akan diperbarui setelah notifikasi Midtrans diterima.</li>
            </ol>
            <div class="d-grid gap-2 mt-4">
              <?php if ($snapToken !== null): ?>
                <button class="btn btn-primary btn-lg" id="pay-button" type="button">Bayar Sekarang</button>
                <small class="text-secondary">Token siap digunakan. Jika browser memblokir popup, gunakan tombol alternatif.</small>
                <?php if ($snapUrl !== null): ?>
                  <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($snapUrl); ?>" target="_blank" rel="noopener">Buka Halaman Midtrans</a>
                <?php endif; ?>
              <?php else: ?>
                <div class="alert alert-warning mb-0">Token pembayaran belum tersedia.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php if ($snapToken !== null): ?>
  <script src="<?php echo htmlspecialchars(midtrans_base_url() . '/snap/snap.js'); ?>" data-client-key="<?php echo htmlspecialchars(midtrans_client_key()); ?>"></script>
  <script>
    const payButton = document.getElementById('pay-button');
    if (payButton && window.snap) {
      payButton.addEventListener('click', function () {
        window.snap.pay('<?php echo addslashes($snapToken); ?>', {
          onSuccess: function () {
            window.location.href = '<?php echo htmlspecialchars(app_url('user/payment.php')); ?>';
          },
          onPending: function () {
            window.location.href = '<?php echo htmlspecialchars(app_url('user/payment.php')); ?>';
          },
          onError: function () {
            alert('Pembayaran gagal diproses.');
          },
          onClose: function () {
            window.location.href = '<?php echo htmlspecialchars(app_url('user/payment.php')); ?>';
          }
        });
      });
    }
  </script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>