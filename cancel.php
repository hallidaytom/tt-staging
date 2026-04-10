<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/email.php';

loadEnv(__DIR__ . '/../.env');

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$status = null;
$message = '';
$booking = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = trim($_POST['token'] ?? '');
    if ($token === '') {
        $status = 'error';
        $message = 'Invalid cancellation token.';
    } else {
        $result = cancelBooking($token);
        if ($result['ok'] ?? false) {
            $status = 'success';
            $booking = $result['booking'];
            $message = 'Your booking has been cancelled. We hope to see you soon.';
            sendCancellationConfirmation($booking);
        } else {
            $status = 'error';
            $error = $result['error'] ?? 'unknown';
            if ($error === 'already_cancelled') {
                $message = 'This booking was already cancelled.';
                $booking = $result['booking'] ?? null;
            } elseif ($error === 'not_found') {
                $message = 'We could not find a booking for that link.';
            } else {
                $message = 'Unable to cancel this booking. Please contact the studio.';
            }
        }
    }
} elseif ($token !== '') {
    $booking = fetchBookingByToken($token);
    if (!$booking) {
        $status = 'error';
        $message = 'We could not find a booking for that link.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cancel Booking — Focus Gym Mulwala</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>
<body data-page="cancel">
<header class="site-header">
    <img src="/assets/img/logo.jpg" alt="Focus Gym Mulwala" class="site-header-logo" />
    <div class="site-header-text">
        <p class="eyebrow">Yarrawonga · 24/7</p>
        <h1>Cancel Booking</h1>
    </div>
</header>

<main class="cancel-layout">
    <?php if ($status === 'success'): ?>
        <div class="card success">
            <div class="success-icon">✓</div>
            <h2>Booking Cancelled</h2>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if ($booking): ?>
                <ul class="booking-summary">
                    <li><strong>Date:</strong> <?= htmlspecialchars(formatDate($booking['booking_date']), ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>Time:</strong> <?= htmlspecialchars(formatSlotTime((int)$booking['slot_hour']), ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>Reformer:</strong> <?= htmlspecialchars(($booking['reformer'] === 'both' ? 'Whole Studio' : 'Reformer ' . $booking['reformer']), ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            <?php endif; ?>
        </div>
    <?php elseif ($booking): ?>
        <div class="card">
            <h2>Booking Details</h2>
            <ul class="booking-summary">
                <li><strong>Name:</strong> <?= htmlspecialchars($booking['member_name'], ENT_QUOTES, 'UTF-8'); ?></li>
                <li><strong>Date:</strong> <?= htmlspecialchars(formatDate($booking['booking_date']), ENT_QUOTES, 'UTF-8'); ?></li>
                <li><strong>Time:</strong> <?= htmlspecialchars(formatSlotTime((int)$booking['slot_hour']), ENT_QUOTES, 'UTF-8'); ?></li>
                <li><strong>Reformer:</strong> <?= htmlspecialchars(($booking['reformer'] === 'both' ? 'Whole Studio' : 'Reformer ' . $booking['reformer']), ENT_QUOTES, 'UTF-8'); ?></li>
            </ul>
            <?php if (($booking['status'] ?? '') === 'cancelled'): ?>
                <p class="muted">This booking is already cancelled.</p>
            <?php else: ?>
                <form method="post" class="cancel-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
                    <button type="submit" class="btn btn-primary">Confirm Cancellation</button>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card error">
            <h2>Hmm…</h2>
            <p><?= htmlspecialchars($message ?: 'Nothing to cancel here.', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>
</main>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
