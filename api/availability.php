<?php
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';

loadEnv(__DIR__ . '/../../.env');
requireMethod('GET');

$dateParam = $_GET['date'] ?? '';
$tz = new DateTimeZone('Australia/Melbourne');
$today = new DateTime('today', $tz);
$maxDate = (clone $today)->modify('+14 days');

if ($dateParam === '') {
    $dateParam = $today->format('Y-m-d');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $dateParam, $tz);
if (!$dateObj) {
    jsonResponse(['ok' => false, 'error' => 'invalid_date'], 400);
}

if ($dateObj < $today || $dateObj > $maxDate) {
    jsonResponse(['ok' => false, 'error' => 'out_of_range'], 422);
}

$slots = getAvailability($dateObj->format('Y-m-d'));

jsonResponse([
    'ok' => true,
    'date' => $dateObj->format('Y-m-d'),
    'slots' => $slots,
]);
