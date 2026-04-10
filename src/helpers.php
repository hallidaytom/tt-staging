<?php

declare(strict_types=1);

/**
 * Loads environment variables from a .env file into $_ENV/putenv.
 */
function loadEnv(string $path): void
{
    static $loadedFiles = [];

    if (isset($loadedFiles[$path])) {
        return;
    }

    if (!is_file($path)) {
        $loadedFiles[$path] = true;
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        $loadedFiles[$path] = true;
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $_ENV[$key] = $value;
        putenv(sprintf('%s=%s', $key, $value));
    }

    $loadedFiles[$path] = true;
}

function env(string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    return $value;
}

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireMethod(string $method): void
{
    $current = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (strcasecmp($current, $method) !== 0) {
        header('Allow: ' . strtoupper($method));
        jsonResponse(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }
}

function generateToken(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function formatSlotTime(int $hour): string
{
    $tz = new DateTimeZone('Australia/Melbourne');
    $dt = new DateTime('today', $tz);
    $dt->setTime($hour, 0, 0);
    return $dt->format('g:i A');
}

function formatDate(string $date): string
{
    $tz = new DateTimeZone('Australia/Melbourne');
    $dt = DateTime::createFromFormat('Y-m-d', $date, $tz);
    if (!$dt) {
        return $date;
    }
    return $dt->format('l j F Y');
}

function sanitise(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
