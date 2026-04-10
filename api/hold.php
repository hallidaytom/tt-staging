<?php
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';

loadEnv(__DIR__ . '/../../.env');
requireMethod('POST');

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    jsonResponse(['ok' => false, 'error' => 'invalid_json'], 400);
}

$date = $payload['date'] ?? '';
$hour = isset($payload['hour']) ? (int)$payload['hour'] : -1;
$reformer = (string)($payload['reformer'] ?? '');

$tz = new DateTimeZone('Australia/Melbourne');
$today = new DateTime('today', $tz);
$maxDate = (clone $today)->modify('+14 days');
$dateObj = DateTime::createFromFormat('Y-m-d', $date, $tz);
if (!$dateObj || $dateObj < $today || $dateObj > $maxDate) {
    jsonResponse(['ok' => false, 'error' => 'invalid_date'], 422);
}

if ($hour < 0 || $hour > 23) {
    jsonResponse(['ok' => false, 'error' => 'invalid_hour'], 422);
}

$holdToken = generateToken(16);
$result = placeHold($dateObj->format('Y-m-d'), $hour, $reformer, $holdToken);

$ok = $result['ok'] ?? false;
$statusCode = $ok ? 200 : 409;
if (!$ok && in_array($result['error'] ?? '', ['invalid_reformer', 'invalid_hour'], true)) {
    $statusCode = 422;
}

jsonResponse($result, $statusCode);
