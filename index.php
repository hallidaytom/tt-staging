<?php
require_once __DIR__ . '/../src/helpers.php';
loadEnv(__DIR__ . '/../.env');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Focus Gym Mulwala — Reformer Booking</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>
<body data-page="landing">

    <header class="site-header">
        <img src="/assets/img/logo.jpg" alt="Focus Gym Mulwala" class="site-header-logo" />
        <div class="site-header-text">
            <p class="eyebrow">Yarrawonga · 24/7</p>
            <h1>Reformer Booking</h1>
        </div>
    </header>

    <main class="landing">

        <!-- Hero -->
        <section class="landing-hero">
            <div class="landing-hero-inner">
                <h2>Pilates Reformer<br>Bookings Online</h2>
                <p>Reserve your reformer at Focus Gym Mulwala in seconds. Studio-grade equipment, flexible sessions, available to active reformer members.</p>
                <a href="/book.php" class="btn btn-primary">Book a Session</a>
            </div>
        </section>

        <!-- Equipment -->
        <section class="landing-section">
            <p class="section-label">Our Equipment</p>
            <h2 class="section-title">Studio-Grade Reformers</h2>
            <p class="section-sub">We run <strong>Your Reformer</strong> beds — the same equipment found in professional Pilates studios, built for serious training.</p>

            <div class="equipment-block">
                <div class="equipment-image">
                    <img
                        src="/assets/img/reformer.jpg"
                        alt="Pilates reformer equipment at Focus Gym"
                        loading="lazy"
                    />
                </div>
                <div class="equipment-specs">
                    <div class="spec-item">
                        <div class="spec-icon">🪵</div>
                        <div class="spec-text">
                            <strong>Solid Maple Timber Frame</strong>
                            <span>Premium hardwood construction — built for durability and a clean studio aesthetic.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon">🔩</div>
                        <div class="spec-text">
                            <strong>5 German-Made Springs</strong>
                            <span>2× heavy, 2× medium, 1× light — precise resistance control for any skill level.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon">🦶</div>
                        <div class="spec-text">
                            <strong>5-Position Foot Bar</strong>
                            <span>High-grip, easily adjustable — sets up perfectly for your height and workout.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon">🛏</div>
                        <div class="spec-text">
                            <strong>Padded Carriage &amp; Headrest</strong>
                            <span>Cushioned support throughout your flow, with a 3-position adjustable headrest.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon">📐</div>
                        <div class="spec-text">
                            <strong>Adjustable Height Legs</strong>
                            <span>Telescopic legs raise the frame up to 36cm — ideal for accessibility and flow transitions.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <hr class="landing-divider" />

        <!-- Benefits -->
        <section class="landing-section benefits-section" style="max-width:100%; padding-left:0; padding-right:0;">
            <div style="max-width:960px; margin:0 auto; padding: 3rem 1.5rem;">
                <p class="section-label">Why Reformer?</p>
                <h2 class="section-title">Built for Every Body</h2>
                <p class="section-sub">Reformer Pilates is one of the most effective full-body workouts around — low impact, high results.</p>

                <div class="benefits-grid">
                    <div class="benefit-card">
                        <span class="benefit-emoji">💪</span>
                        <h3>Full-Body Strength</h3>
                        <p>Every session works your core, legs, arms and back simultaneously — building functional strength without heavy weights.</p>
                    </div>
                    <div class="benefit-card">
                        <span class="benefit-emoji">🧘</span>
                        <h3>Flexibility &amp; Mobility</h3>
                        <p>The spring resistance assists and challenges your range of motion, progressively improving flexibility and joint health.</p>
                    </div>
                    <div class="benefit-card">
                        <span class="benefit-emoji">🦴</span>
                        <h3>Low Impact</h3>
                        <p>Gentle on joints and suitable for all fitness levels — from injury rehab to elite athletes looking to complement training.</p>
                    </div>
                    <div class="benefit-card">
                        <span class="benefit-emoji">🎯</span>
                        <h3>Core Focus</h3>
                        <p>The carriage keeps you honest — precise, controlled movement that activates stabilising muscles you didn't know you had.</p>
                    </div>
                    <div class="benefit-card">
                        <span class="benefit-emoji">🔥</span>
                        <h3>Burn &amp; Tone</h3>
                        <p>Don't let the calm fool you. A 45-minute reformer session is a serious calorie burn with visible toning results over time.</p>
                    </div>
                    <div class="benefit-card">
                        <span class="benefit-emoji">📅</span>
                        <h3>Book Your Time</h3>
                        <p>We've got 2 reformers available to active members. Pick your slot, come in, train on your terms — no class schedule required.</p>
                    </div>
                </div>
            </div>
        </section>


    </main>

    <!-- CTA Footer -->
    <div class="landing-cta">
        <h2>Ready to Book?</h2>
        <p>Active reformer members can reserve a machine online, any time.</p>
        <a href="/book.php" class="btn btn-primary">Book a Session</a>
    </div>

    <footer class="site-footer">
        Focus Gym Mulwala · Member-only reformer access · <a href="https://yourreformer.com.au" target="_blank" rel="noopener">Equipment by Your Reformer</a>
    </footer>

    <script src="/assets/js/app.js" defer></script>
</body>
</html>
