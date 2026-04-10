<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function sendBookingConfirmation(array $booking): void
{
    $to = $booking['member_email'] ?? null;
    if (!$to) {
        return;
    }

    $dateText = formatDate($booking['booking_date']);
    $timeText = formatSlotTime((int)$booking['slot_hour']);
    $cancelLink = rtrim((string)env('BASE_URL', ''), '/') . '/cancel.php?token=' . urlencode($booking['cancel_token']);

    $subject = sprintf('Your Reformer Booking Confirmation — %s %s', $dateText, $timeText);
    $endTime = formatSlotTime(((int)$booking['slot_hour'] + 1) % 24);
    $reformerLabel = $booking['reformer'] === 'both' ? 'Whole Studio (both reformers)' : 'Reformer ' . $booking['reformer'];

    $html = <<<HTML
    <p>Hi {$booking['member_name']},</p>
    <p>Your Pilates reformer booking at Focus Gym Mulwala is confirmed.</p>
    <ul>
        <li><strong>Date:</strong> {$dateText}</li>
        <li><strong>Time:</strong> {$timeText} – {$endTime}</li>
        <li><strong>Reformer:</strong> {$reformerLabel}</li>
    </ul>
    <p>If you can no longer attend, please cancel here:<br>
        <a href="{$cancelLink}">{$cancelLink}</a>
    </p>
    <p>See you in the studio!<br>Focus Gym Mulwala</p>
HTML;

    $attachments = [
        [
            'filename' => 'focus-gym-booking.ics',
            'content' => base64_encode(buildCalendarInvite($booking)),
            'contentType' => 'text/calendar',
        ],
    ];

    sendResendEmail($subject, $html, $to, $attachments);
}

function sendCancellationConfirmation(array $booking): void
{
    $to = $booking['member_email'] ?? null;
    if (!$to) {
        return;
    }

    $dateText = formatDate($booking['booking_date']);
    $timeText = formatSlotTime((int)$booking['slot_hour']);
    $subject = sprintf('Your Reformer Booking Has Been Cancelled — %s %s', $dateText, $timeText);

    $html = <<<HTML
    <p>Hi {$booking['member_name']},</p>
    <p>Your Pilates reformer booking on {$dateText} at {$timeText} has been cancelled successfully. The reformer has been released to other members.</p>
    <p>Need to reschedule? Head back to the booking page at Focus Gym.</p>
    <p>— Focus Gym Mulwala</p>
HTML;

    sendResendEmail($subject, $html, $to);
}

function sendResendEmail(string $subject, string $html, string $to, array $attachments = []): void
{
    $apiKey = env('RESEND_API_KEY');
    $from = env('FROM_EMAIL');

    if (!$apiKey || !$from) {
        error_log('Resend not configured. Skipping email send.');
        return;
    }

    $payload = [
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $html,
    ];

    if (!empty($attachments)) {
        $payload['attachments'] = array_map(static function ($attachment) {
            return [
                'filename' => $attachment['filename'],
                'content' => $attachment['content'],
                'content_type' => $attachment['contentType'] ?? 'application/octet-stream',
            ];
        }, $attachments);
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error || $statusCode >= 400) {
        error_log('Resend API error: ' . ($error ?: $response));
    }
}

function buildCalendarInvite(array $booking): string
{
    $tz = new DateTimeZone('Australia/Melbourne');
    $start = DateTime::createFromFormat('Y-m-d H:i', sprintf('%s %02d:00', $booking['booking_date'], (int)$booking['slot_hour']), $tz);
    if (!$start) {
        $start = new DateTime('now', $tz);
    }
    $end = clone $start;
    $end->modify('+1 hour');

    $uid = ($booking['cancel_token'] ?? generateToken(8)) . '@focusgym';

    $reformerLabel = ($booking['reformer'] ?? '') === 'both'
        ? 'Whole Studio'
        : 'Reformer ' . strtoupper((string)$booking['reformer']);

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Focus Gym//Reformer Booking//EN',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        'DTSTART;TZID=Australia/Melbourne:' . $start->format('Ymd\THis'),
        'DTEND;TZID=Australia/Melbourne:' . $end->format('Ymd\THis'),
        'SUMMARY:' . $reformerLabel . ' — Focus Gym Mulwala',
        'DESCRIPTION:Your Pilates reformer booking at Focus Gym Mulwala.',
        'LOCATION:Focus Gym Mulwala',
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    return implode("\r\n", $lines);
}
