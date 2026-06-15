<?php

require_once __DIR__ . '/../config/midtrans.php';

function midtrans_config(): array
{
    return require __DIR__ . '/../config/midtrans.php';
}

function midtrans_base_url(): string
{
    $config = midtrans_config();

    return !empty($config['is_production'])
        ? 'https://app.midtrans.com'
        : 'https://app.sandbox.midtrans.com';
}

function midtrans_server_key(): string
{
    $config = midtrans_config();
    return trim((string) ($config['server_key'] ?? ''));
}

function midtrans_client_key(): string
{
    $config = midtrans_config();
    return trim((string) ($config['client_key'] ?? ''));
}

function midtrans_is_configured(): bool
{
    $serverKey = midtrans_server_key();
    $clientKey = midtrans_client_key();

    return $serverKey !== ''
        && $clientKey !== ''
        && $serverKey !== 'MIDTRANS_SERVER_KEY'
        && $clientKey !== 'MIDTRANS_CLIENT_KEY';
}

function midtrans_create_snap_token(array $payload): array
{
    if (!midtrans_is_configured()) {
        throw new RuntimeException('Konfigurasi Midtrans belum disiapkan.');
    }

    $ch = curl_init(midtrans_base_url() . '/snap/v1/transactions');

    if ($ch === false) {
        throw new RuntimeException('Gagal memulai koneksi Midtrans.');
    }

    $authorization = base64_encode(midtrans_server_key() . ':');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . $authorization,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException('Midtrans request gagal: ' . $error);
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Respons Midtrans tidak valid.');
    }

    if ($statusCode < 200 || $statusCode >= 300) {
        $message = $decoded['error_messages'][0] ?? ($decoded['status_message'] ?? 'Gagal membuat Snap token.');
        throw new RuntimeException($message);
    }

    return $decoded;
}

function midtrans_verify_signature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
{
    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . midtrans_server_key());

    return hash_equals($expected, $signatureKey);
}