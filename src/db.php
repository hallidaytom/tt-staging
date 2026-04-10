<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function getDb(): SQLite3
{
    static $db = null;

    if ($db instanceof SQLite3) {
        return $db;
    }

    $rawPath = env('DB_PATH') ?? '../data/bookings.db';
    // Resolve relative paths from the project root (one level above src/)
    $projectRoot = dirname(__DIR__);
    $dbPath = $rawPath[0] === '/' ? $rawPath : $projectRoot . '/' . ltrim($rawPath, './');
    $directory = dirname($dbPath);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $db = new SQLite3($dbPath);
    $db->enableExceptions(true);
    $db->busyTimeout(5000);
    $db->exec('PRAGMA foreign_keys = ON;');

    initialiseSchema($db);

    return $db;
}

function initialiseSchema(SQLite3 $db): void
{
    $schema = <<<SQL
    CREATE TABLE IF NOT EXISTS bookings (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      member_code   TEXT NOT NULL,
      member_name   TEXT NOT NULL,
      member_email  TEXT NOT NULL,
      booking_date  TEXT NOT NULL,
      slot_hour     INTEGER NOT NULL,
      reformer      TEXT NOT NULL,
      status        TEXT NOT NULL DEFAULT 'confirmed',
      cancel_token  TEXT UNIQUE,
      hold_expires_at TEXT,
      created_at    TEXT NOT NULL,
      cancelled_at  TEXT
    );

    CREATE TABLE IF NOT EXISTS members (
      code     TEXT PRIMARY KEY,
      added_at TEXT NOT NULL
    );

    CREATE INDEX IF NOT EXISTS idx_bookings_date ON bookings(booking_date, status);
SQL;

    $db->exec($schema);
}

function clearExpiredHolds(?SQLite3 $db = null): void
{
    $db = $db ?? getDb();
    $db->exec("DELETE FROM bookings WHERE status = 'hold' AND hold_expires_at IS NOT NULL AND hold_expires_at < datetime('now')");
}

function normaliseReformer(string $value): string
{
    $value = strtolower(trim($value));
    if (!in_array($value, ['1', '2', 'both'], true)) {
        throw new InvalidArgumentException('Invalid reformer selection.');
    }
    return $value;
}

function getAvailability(string $date): array
{
    $db = getDb();
    clearExpiredHolds($db);

    $slots = [];
    for ($hour = 0; $hour < 24; $hour++) {
        $slots[$hour] = [
            'reformer1' => 'available',
            'reformer2' => 'available',
        ];
    }

    $stmt = $db->prepare("SELECT slot_hour, reformer, status FROM bookings WHERE booking_date = :date AND status IN ('confirmed', 'hold')");
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $hour = (int)$row['slot_hour'];
        $status = $row['status'] === 'hold' ? 'hold' : 'booked';
        $reformer = $row['reformer'];

        if (!isset($slots[$hour])) {
            continue;
        }

        if ($reformer === 'both') {
            $slots[$hour]['reformer1'] = $status;
            $slots[$hour]['reformer2'] = $status;
        } elseif ($reformer === '1') {
            $slots[$hour]['reformer1'] = $status;
        } elseif ($reformer === '2') {
            $slots[$hour]['reformer2'] = $status;
        }
    }

    return $slots;
}

function placeHold(string $date, int $hour, string $reformer, string $holdToken): array
{
    $db = getDb();
    clearExpiredHolds($db);

    try {
        $reformer = normaliseReformer($reformer);
    } catch (InvalidArgumentException $exception) {
        return ['ok' => false, 'error' => 'invalid_reformer'];
    }

    if ($hour < 0 || $hour > 23) {
        return ['ok' => false, 'error' => 'invalid_hour'];
    }

    $db->exec('BEGIN IMMEDIATE');

    try {
        if (slotHasConflict($db, $date, $hour, $reformer)) {
            $db->exec('ROLLBACK');
            return ['ok' => false, 'error' => 'slot_taken'];
        }

        $expiresAt = gmdate('Y-m-d H:i:s', time() + 600);
        $createdAt = gmdate('Y-m-d H:i:s');

        $stmt = $db->prepare("INSERT INTO bookings (member_code, member_name, member_email, booking_date, slot_hour, reformer, status, cancel_token, hold_expires_at, created_at) VALUES (:code, :name, :email, :date, :hour, :reformer, 'hold', :token, :expires, :created)");
        $stmt->bindValue(':code', '__HOLD__', SQLITE3_TEXT);
        $stmt->bindValue(':name', 'Hold Pending', SQLITE3_TEXT);
        $stmt->bindValue(':email', 'hold@pending.local', SQLITE3_TEXT);
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
        $stmt->bindValue(':hour', $hour, SQLITE3_INTEGER);
        $stmt->bindValue(':reformer', $reformer, SQLITE3_TEXT);
        $stmt->bindValue(':token', $holdToken, SQLITE3_TEXT);
        $stmt->bindValue(':expires', $expiresAt, SQLITE3_TEXT);
        $stmt->bindValue(':created', $createdAt, SQLITE3_TEXT);
        $stmt->execute();

        $db->exec('COMMIT');
        return ['ok' => true, 'holdToken' => $holdToken];
    } catch (Throwable $e) {
        $db->exec('ROLLBACK');
        error_log('Hold placement failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'server_error'];
    }
}

function slotHasConflict(SQLite3 $db, string $date, int $hour, string $requestedReformer): bool
{
    $stmt = $db->prepare("SELECT reformer FROM bookings WHERE booking_date = :date AND slot_hour = :hour AND status IN ('confirmed', 'hold')");
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':hour', $hour, SQLITE3_INTEGER);
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $existing = $row['reformer'];

        if ($existing === 'both' || $requestedReformer === 'both') {
            return true;
        }

        if ($existing === $requestedReformer) {
            return true;
        }
    }

    return false;
}

function releaseHold(string $holdToken): void
{
    $db = getDb();
    $stmt = $db->prepare("DELETE FROM bookings WHERE status = 'hold' AND cancel_token = :token");
    $stmt->bindValue(':token', $holdToken, SQLITE3_TEXT);
    $stmt->execute();
}

function confirmBooking(array $data): array
{
    $required = ['holdToken', 'memberCode', 'memberName', 'memberEmail', 'date', 'hour', 'reformer'];
    foreach ($required as $key) {
        if (!array_key_exists($key, $data)) {
            return ['ok' => false, 'error' => 'missing_' . $key];
        }
    }

    $db = getDb();
    clearExpiredHolds($db);

    $memberCode = strtoupper(trim((string)$data['memberCode']));

    if (!memberCodeIsValid($memberCode)) {
        releaseHold($data['holdToken']);
        return ['ok' => false, 'error' => 'invalid_code'];
    }

    $db->exec('BEGIN IMMEDIATE');

    try {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE cancel_token = :token AND status = 'hold' LIMIT 1");
        $stmt->bindValue(':token', $data['holdToken'], SQLITE3_TEXT);
        $hold = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if (!$hold) {
            $db->exec('ROLLBACK');
            return ['ok' => false, 'error' => 'hold_not_found'];
        }

        if ((int)$hold['slot_hour'] !== (int)$data['hour'] || $hold['booking_date'] !== $data['date'] || $hold['reformer'] !== $data['reformer']) {
            $db->exec('ROLLBACK');
            releaseHold($data['holdToken']);
            return ['ok' => false, 'error' => 'hold_mismatch'];
        }

        $expiresAt = $hold['hold_expires_at'];
        if ($expiresAt !== null) {
            $expires = DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt, new DateTimeZone('UTC'));
            $now = new DateTime('now', new DateTimeZone('UTC'));
            if ($expires && $expires < $now) {
                $db->exec('ROLLBACK');
                releaseHold($data['holdToken']);
                return ['ok' => false, 'error' => 'hold_expired'];
            }
        }

        $cancelToken = generateToken(16);
        $stmt = $db->prepare("UPDATE bookings SET member_code = :code, member_name = :name, member_email = :email, status = 'confirmed', cancel_token = :cancel, hold_expires_at = NULL, created_at = :created WHERE id = :id");
        $stmt->bindValue(':code', $memberCode, SQLITE3_TEXT);
        $stmt->bindValue(':name', trim((string)$data['memberName']), SQLITE3_TEXT);
        $stmt->bindValue(':email', trim((string)$data['memberEmail']), SQLITE3_TEXT);
        $stmt->bindValue(':cancel', $cancelToken, SQLITE3_TEXT);
        $stmt->bindValue(':created', gmdate('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->bindValue(':id', (int)$hold['id'], SQLITE3_INTEGER);
        $stmt->execute();

        $db->exec('COMMIT');

        $booking = fetchBookingByToken($cancelToken);
        return ['ok' => true, 'cancelToken' => $cancelToken, 'booking' => $booking];
    } catch (Throwable $e) {
        $db->exec('ROLLBACK');
        error_log('Booking confirmation failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'server_error'];
    }
}

function fetchBookingByToken(string $token): ?array
{
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM bookings WHERE cancel_token = :token LIMIT 1");
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    return $result ?: null;
}

function cancelBooking(string $token): array
{
    $db = getDb();

    $stmt = $db->prepare("SELECT * FROM bookings WHERE cancel_token = :token LIMIT 1");
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $booking = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$booking) {
        return ['ok' => false, 'error' => 'not_found'];
    }

    if ($booking['status'] === 'cancelled') {
        return ['ok' => false, 'error' => 'already_cancelled', 'booking' => $booking];
    }

    if ($booking['status'] !== 'confirmed') {
        return ['ok' => false, 'error' => 'invalid_status'];
    }

    $cancelledAt = gmdate('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = :cancelled WHERE id = :id");
    $stmt->bindValue(':cancelled', $cancelledAt, SQLITE3_TEXT);
    $stmt->bindValue(':id', (int)$booking['id'], SQLITE3_INTEGER);
    $stmt->execute();

    $booking['status'] = 'cancelled';
    $booking['cancelled_at'] = $cancelledAt;

    return ['ok' => true, 'booking' => $booking];
}

function getWeeklyReport(): array
{
    $db = getDb();
    $startDate = (new DateTime('now', new DateTimeZone('Australia/Melbourne')))
        ->modify('-6 days')
        ->format('Y-m-d');

    $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_date >= :start AND status IN ('confirmed', 'cancelled') ORDER BY booking_date DESC, slot_hour ASC");
    $stmt->bindValue(':start', $startDate, SQLITE3_TEXT);
    $result = $stmt->execute();

    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    return $rows;
}

function replaceMembers(array $codes): int
{
    $db = getDb();
    $db->exec('BEGIN');
    try {
        $db->exec('DELETE FROM members');
        $stmt = $db->prepare('INSERT INTO members (code, added_at) VALUES (:code, :added)');
        $added = gmdate('Y-m-d H:i:s');
        $count = 0;

        $uniqueCodes = [];
        foreach ($codes as $code) {
            $clean = strtoupper(trim($code));
            if ($clean === '') {
                continue;
            }
            $uniqueCodes[$clean] = true;
        }

        foreach (array_keys($uniqueCodes) as $clean) {
            $stmt->bindValue(':code', $clean, SQLITE3_TEXT);
            $stmt->bindValue(':added', $added, SQLITE3_TEXT);
            $stmt->execute();
            $stmt->reset();
            $count++;
        }

        $db->exec('COMMIT');
        return $count;
    } catch (Throwable $e) {
        $db->exec('ROLLBACK');
        throw $e;
    }
}

function memberCodeIsValid(string $code): bool
{
    $db = getDb();
    $stmt = $db->prepare('SELECT 1 FROM members WHERE code = :code LIMIT 1');
    $stmt->bindValue(':code', strtoupper(trim($code)), SQLITE3_TEXT);
    $exists = $stmt->execute()->fetchArray(SQLITE3_NUM);
    return $exists !== false;
}

function getMemberStats(): array
{
    $db = getDb();
    $countRow = (int)$db->querySingle('SELECT COUNT(*) FROM members');
    $lastUpdated = $db->querySingle('SELECT MAX(added_at) FROM members');

    return [
        'count' => $countRow,
        'last_updated' => $lastUpdated,
    ];
}

function getAllMemberCodes(): array
{
    $db = getDb();
    $result = $db->query('SELECT code FROM members ORDER BY code ASC');
    $codes = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $codes[] = $row['code'];
    }
    return $codes;
}
