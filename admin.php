<?php
session_start();

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/db.php';

loadEnv(__DIR__ . '/../.env');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$isAuthed = $_SESSION['admin_authed'] ?? false;
$errors = [];
$success = '';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_POST['action'] ?? null;

if ($method === 'POST') {
    if ($action === 'login') {
        $password = $_POST['password'] ?? '';
        if (hash_equals((string)env('ADMIN_PASSWORD', ''), $password)) {
            $_SESSION['admin_authed'] = true;
            $isAuthed = true;
            $success = 'Logged in successfully.';
        } else {
            $errors[] = 'Incorrect password.';
        }
    } elseif ($action === 'logout' && $isAuthed) {
        session_destroy();
        header('Location: /admin.php');
        exit;
    } elseif ($action === 'update_members' && $isAuthed) {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            $errors[] = 'Session expired. Please refresh.';
        } else {
            $rawCodes = $_POST['memberCodes'] ?? '';
            $codes = preg_split('/\r?\n/', $rawCodes) ?: [];
            try {
                $count = replaceMembers($codes);
                $success = sprintf('Saved %d member codes.', $count);
            } catch (Throwable $e) {
                $errors[] = 'Unable to save member codes.';
                error_log('Member import failed: ' . $e->getMessage());
            }
        }
    }
}

$memberStats = $isAuthed ? getMemberStats() : ['count' => 0, 'last_updated' => null];
$memberCodes = $isAuthed ? implode("\n", getAllMemberCodes()) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin — Focus Gym Reformer</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>
<body data-page="admin">
<header class="site-header">
    <img src="/assets/img/logo.jpg" alt="Focus Gym Mulwala" class="site-header-logo" />
    <div class="site-header-text">
        <p class="eyebrow">Yarrawonga · 24/7</p>
        <h1>Admin Panel</h1>
    </div>
</header>

<main class="admin-layout">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!$isAuthed): ?>
        <section class="card admin-card">
            <h2>Admin Login</h2>
            <form method="post" class="admin-form">
                <input type="hidden" name="action" value="login" />
                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </section>
    <?php else: ?>
        <section class="card admin-card">
            <header class="card-header">
                <div>
                    <h2>Member Codes</h2>
                    <p class="muted">
                        <?= htmlspecialchars((string)$memberStats['count'], ENT_QUOTES, 'UTF-8'); ?> active ·
                        Last updated <?= htmlspecialchars($memberStats['last_updated'] ? formatDate(substr($memberStats['last_updated'], 0, 10)) : 'n/a', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="logout" />
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </header>

            <form method="post" class="admin-form">
                <input type="hidden" name="action" value="update_members" />
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
                <label for="memberCodes">Paste member codes (one per line)</label>
                <textarea id="memberCodes" name="memberCodes" rows="10"><?= htmlspecialchars($memberCodes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <button type="submit" class="btn btn-primary">Save Codes</button>
            </form>

            <div class="admin-actions">
                <a class="btn btn-secondary" href="/api/report.php" target="_blank" rel="noopener">Download Weekly Report</a>
            </div>
        </section>
    <?php endif; ?>
</main>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
