<?php
/**
 * General helper functions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

function getSiteSettings(): array
{
    $db = getDB();
    $stmt = $db->query('SELECT * FROM site_settings ORDER BY id ASC LIMIT 1');
    $settings = $stmt->fetch();

    if (!$settings) {
        return [
            'event_name'                   => 'AI Extreme 2026',
            'event_date'                   => '2026-10-14',
            'problem_release_date'         => '2026-10-02',
            'early_bird_fee'               => 200,
            'early_bird_deadline'          => '2026-09-20',
            'regular_fee'                  => 250,
            'upi_id'                       => 'YOUR-UPI-ID@upi',
            'qr_image'                     => 'assets/images/payment-qr.png',
            'official_email'               => 'OFFICIAL_EMAIL',
            'official_phone'               => 'OFFICIAL_PHONE',
            'problem_statements_published' => 0,
        ];
    }

    return $settings;
}

function calculateRegistrationFee(?array $settings = null): float
{
    $settings = $settings ?? getSiteSettings();
    $today = new DateTime('now', new DateTimeZone(TIMEZONE));
    $deadline = new DateTime($settings['early_bird_deadline'], new DateTimeZone(TIMEZONE));
    $deadline->setTime(23, 59, 59);

    if ($today <= $deadline) {
        return (float) $settings['early_bird_fee'];
    }

    return (float) $settings['regular_fee'];
}

function isEarlyBird(?array $settings = null): bool
{
    $settings = $settings ?? getSiteSettings();
    $today = new DateTime('now', new DateTimeZone(TIMEZONE));
    $deadline = new DateTime($settings['early_bird_deadline'], new DateTimeZone(TIMEZONE));
    $deadline->setTime(23, 59, 59);
    return $today <= $deadline;
}

function generateRegistrationId(PDO $db): string
{
    $prefix = 'AIX26-';

    $stmt = $db->query("SELECT registration_id FROM teams WHERE registration_id LIKE 'AIX26-%' ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();

    if ($last) {
        $num = (int) substr($last['registration_id'], 6);
        $next = $num + 1;
    } else {
        $next = 1;
    }

    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function getTeamMembers(int $teamId): array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM team_members WHERE team_id = ? ORDER BY is_leader DESC, id ASC');
    $stmt->execute([$teamId]);
    return $stmt->fetchAll();
}

function getTeamByRegistrationId(string $registrationId): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM teams WHERE registration_id = ?');
    $stmt->execute([$registrationId]);
    $team = $stmt->fetch();
    return $team ?: null;
}

function countGenderMembers(array $members): array
{
    $counts = ['Male' => 0, 'Female' => 0, 'Other' => 0, 'Prefer not to say' => 0];

    foreach ($members as $member) {
        $gender = $member['gender'] ?? '';
        if (isset($counts[$gender])) {
            $counts[$gender]++;
        }
    }

    return [
        'boys'  => $counts['Male'],
        'girls' => $counts['Female'],
        'other' => $counts['Other'] + $counts['Prefer not to say'],
        'total' => count($members),
    ];
}

function getPublishedProblemStatements(): array
{
    $settings = getSiteSettings();
    if (empty($settings['problem_statements_published'])) {
        return [];
    }

    $db = getDB();
    $stmt = $db->query("SELECT * FROM problem_statements WHERE status = 'published' ORDER BY id ASC");
    return $stmt->fetchAll();
}

function formatDate(string $date, string $format = 'd M Y'): string
{
    $dt = new DateTime($date, new DateTimeZone(TIMEZONE));
    return $dt->format($format);
}

function formatCurrency(float $amount): string
{
    return '₹' . number_format($amount, 0);
}

function statusBadgeClass(string $status): string
{
    return match (strtoupper($status)) {
        'PENDING'  => 'badge-pending',
        'VERIFIED', 'APPROVED' => 'badge-success',
        'REJECTED' => 'badge-danger',
        default    => 'badge-pending',
    };
}

function saveUploadedPayment(array $file): string
{
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir = UPLOAD_PATH;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to save payment screenshot.');
    }

    return UPLOAD_URL . $filename;
}

function getDashboardStats(): array
{
    $db = getDB();

    $totalTeams = (int) $db->query('SELECT COUNT(*) FROM teams')->fetchColumn();
    $totalParticipants = (int) $db->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
    $pendingPayments = (int) $db->query("SELECT COUNT(*) FROM teams WHERE payment_status = 'PENDING'")->fetchColumn();
    $verifiedPayments = (int) $db->query("SELECT COUNT(*) FROM teams WHERE payment_status = 'VERIFIED'")->fetchColumn();
    $rejectedPayments = (int) $db->query("SELECT COUNT(*) FROM teams WHERE payment_status = 'REJECTED'")->fetchColumn();
    $approvedTeams = (int) $db->query("SELECT COUNT(*) FROM teams WHERE registration_status = 'APPROVED'")->fetchColumn();
    $unreadMessages = (int) $db->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();

    return compact(
        'totalTeams', 'totalParticipants', 'pendingPayments',
        'verifiedPayments', 'rejectedPayments', 'approvedTeams', 'unreadMessages'
    );
}
