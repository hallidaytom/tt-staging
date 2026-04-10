<?php
session_start();

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/db.php';

loadEnv(__DIR__ . '/../../.env');

if (empty($_SESSION['admin_authed'])) {
    http_response_code(401);
    echo 'Not authorised';
    exit;
}

$rows = getWeeklyReport();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="focus-gym-weekly-report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Member Code', 'Full Name', 'Email', 'Date', 'Start Time', 'End Time', 'Reformer(s)', 'Booking Status']);

foreach ($rows as $row) {
    $startTime = formatSlotTime((int)$row['slot_hour']);
    $endTime = formatSlotTime(((int)$row['slot_hour'] + 1) % 24);
    $reformer = $row['reformer'] === 'both' ? 'Both Reformers' : 'Reformer ' . $row['reformer'];
    fputcsv($output, [
        $row['member_code'],
        $row['member_name'],
        $row['member_email'],
        $row['booking_date'],
        $startTime,
        $endTime,
        $reformer,
        ucfirst($row['status']),
    ]);
}

fclose($output);
exit;
