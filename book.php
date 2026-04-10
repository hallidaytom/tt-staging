<?php
require_once __DIR__ . '/../src/helpers.php';
loadEnv(__DIR__ . '/../.env');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Book a Reformer — Focus Gym Mulwala</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>
<body data-page="booking">
<header class="site-header">
    <img src="/assets/img/logo.jpg" alt="Focus Gym Mulwala" class="site-header-logo" />
    <div class="site-header-text">
        <p class="eyebrow">Yarrawonga · 24/7</p>
        <h1>Book a Reformer</h1>
    </div>
</header>

<main class="booking-layout">
    <section class="date-strip" id="dateStrip" aria-label="Select a date"></section>

    <section class="slot-grid-section">
        <div class="grid-legend">
            <span class="legend-item"><span class="legend-dot available"></span>Available</span>
            <span class="legend-item"><span class="legend-dot booked"></span>Booked</span>
            <span class="legend-item"><span class="legend-dot hold"></span>On Hold</span>
        </div>
        <div class="slot-grid-scroll">
        <table class="slot-grid" id="slotGrid" aria-live="polite">
            <thead>
            <tr>
                <th>Time</th>
                <th>Reformer 1</th>
                <th>Reformer 2</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="3" class="loading-row">Loading availability…</td>
            </tr>
            </tbody>
        </table>
        </div>
    </section>
</main>

<div class="sticky-action">
    <button id="nextButton" class="btn btn-primary" disabled>
        Next: Enter Details
    </button>
</div>

<div class="modal" id="memberModal" aria-hidden="true">
    <div class="modal-content">
        <button class="modal-close" id="closeModal" aria-label="Close">×</button>
        <h2>Your Details</h2>
        <div id="selectedSummary" class="selected-summary"></div>
        <form id="memberForm" class="member-form" novalidate>
            <div class="form-field">
                <label for="memberCode">Member Code</label>
                <input type="text" id="memberCode" name="memberCode" required maxlength="32" autocomplete="off" />
            </div>
            <div class="form-field">
                <label for="memberName">Full Name</label>
                <input type="text" id="memberName" name="memberName" required maxlength="80" autocomplete="name" />
            </div>
            <div class="form-field">
                <label for="memberEmail">Email</label>
                <input type="email" id="memberEmail" name="memberEmail" required maxlength="120" autocomplete="email" />
            </div>
            <p class="error-text" id="formError" role="alert"></p>
            <button type="submit" class="btn btn-primary" id="confirmBooking" disabled>
                Confirm Booking
            </button>
        </form>
    </div>
</div>

<section class="confirmation-panel" id="confirmationPanel" hidden>
    <div class="success-icon" aria-hidden="true">✓</div>
    <h2>Booking Confirmed</h2>
    <p id="confirmationMessage"></p>
    <button class="btn btn-secondary" id="bookAnother">Book Another Session</button>
</section>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
