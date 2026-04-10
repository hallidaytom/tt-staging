<?php
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/email.php';

loadEnv(__DIR__ . '/../../.env');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));

if ($token === '') {
    jsonResponse(['ok' => false, 'error' => 'missing_token'], 400);
}

if ($method === 'GET') {
    $booking = fetchBookingByToken($token);
    if (!$booking) {
        jsonResponse(['ok' => false, 'error' => 'not_found'], 404);
    }

    jsonResponse([
        'ok' => true,
        'booking' => $booking,
    ]);
}

if ($method === 'POST') {
    $result = cancelBooking($token);
    if ($result['ok'] ?? false) {
        if (!empty($result['booking'])) {
            sendCancellationConfirmation($result['booking']);
        }
        jsonResponse($result);
    }

    $status = ($result['error'] ?? '') === 'not_found' ? 404 : 400;
    jsonResponse($result, $status);
}

jsonResponse(['ok' => false, 'error' => 'method_not_allowed'], 405);
