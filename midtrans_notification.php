<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/midtrans.php';

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid payload']);
    exit;
}

$orderId = (string) ($payload['order_id'] ?? '');
$statusCode = (string) ($payload['status_code'] ?? '');
$grossAmount = (string) ($payload['gross_amount'] ?? '');
$signatureKey = (string) ($payload['signature_key'] ?? '');
$transactionStatus = (string) ($payload['transaction_status'] ?? '');

if ($orderId === '' || !midtrans_verify_signature($orderId, $statusCode, $grossAmount, $signatureKey)) {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid signature']);
    exit;
}

$paymentStatement = $pdo->prepare('SELECT id, booking_id FROM payments WHERE transaction_id = :transaction_id LIMIT 1');
$paymentStatement->execute(['transaction_id' => $orderId]);
$payment = $paymentStatement->fetch();

if (!$payment) {
    http_response_code(404);
    echo json_encode(['message' => 'Payment not found']);
    exit;
}

$paymentStatus = 'pending';
$bookingStatus = 'pending';

if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
    $paymentStatus = 'paid';
    $bookingStatus = 'process';
} elseif (in_array($transactionStatus, ['cancel', 'deny'], true)) {
    $paymentStatus = 'failed';
    $bookingStatus = 'cancelled';
} elseif ($transactionStatus === 'expire') {
    $paymentStatus = 'expired';
    $bookingStatus = 'cancelled';
}

$updatePayment = $pdo->prepare('UPDATE payments SET payment_status = :payment_status WHERE id = :id');
$updatePayment->execute([
    'payment_status' => $paymentStatus,
    'id' => $payment['id'],
]);

$updateBooking = $pdo->prepare('UPDATE bookings SET status = :status WHERE id = :id');
$updateBooking->execute([
    'status' => $bookingStatus,
    'id' => $payment['booking_id'],
]);

echo json_encode(['message' => 'Notification processed']);