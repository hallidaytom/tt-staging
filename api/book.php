<?php
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/email.php';

loadEnv(__DIR__ . '/../../.env');
requireMethod('POST');

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    jsonResponse(['ok' => false, 'error' => 'invalid_json'], 400);
}

$required = ['holdToken', 'memberCode', 'memberName', 'memberEmail', 'date', 'hour', 'reformer'];
foreach ($required as $field) {
    if (!array_key_exists($field, $payload)) {
        jsonResponse(['ok' => false, 'error' => 'missing_' . $field], 422);
    }
}

$result = confirmBooking([
    'holdToken' => (string)$payload['holdToken'],
    'memberCode' => (string)$payload['memberCode'],
    'memberName' => (string)$payload['memberName'],
    'memberEmail' => (string)$payload['memberEmail'],
    'date' => (string)$payload['date'],
    'hour' => (int)$payload['hour'],
    'reformer' => (string)$payload['reformer'],
]);

if ($result['ok'] ?? false) {
    if (!empty($result['booking'])) {
        sendBookingConfirmation($result['booking']);
    }
    jsonResponse($result);
}

$error = $result['error'] ?? 'server_error';
$status = 400;
if ($error === 'invalid_code') {
    $status = 403;
} elseif ($error === 'hold_expired') {
    $status = 410;
} elseif (in_array($error, ['hold_not_found', 'hold_mismatch'], true)) {
    $status = 404;
} elseif ($error === 'slot_taken') {
    $status = 409;
} elseif ($error === 'server_error') {
    $status = 500;
}

jsonResponse($result, $status);
