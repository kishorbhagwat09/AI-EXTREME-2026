<?php
/**
 * Process team registration submission
 */

require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validation.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../registration.php');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['registration_error'] = 'Invalid security token. Please try again.';
    header('Location: ../../registration.php');
    exit;
}

$errors = [];

// Team name
$teamName = trim($_POST['team_name'] ?? '');
if ($err = validateTeamName($teamName)) {
    $errors[] = $err;
}

// Members data
$members = [];
for ($i = 1; $i <= 3; $i++) {
    $prefix = $i === 1 ? 'leader' : "member{$i}";
    $isLeader = ($i === 1) ? 1 : 0;

    $member = [
        'full_name' => trim($_POST["{$prefix}_name"] ?? ''),
        'prn'       => trim($_POST["{$prefix}_prn"] ?? ''),
        'email'     => trim($_POST["{$prefix}_email"] ?? ''),
        'mobile'    => trim($_POST["{$prefix}_mobile"] ?? ''),
        'gender'    => $_POST["{$prefix}_gender"] ?? '',
        'is_leader' => $isLeader,
    ];

    if ($err = validateFullName($member['full_name'])) {
        $errors[] = "Member {$i}: {$err}";
    }
    if ($err = validatePRN($member['prn'])) {
        $errors[] = "Member {$i}: {$err}";
    }
    if ($err = validateEmail($member['email'])) {
        $errors[] = "Member {$i}: {$err}";
    }
    if ($err = validateMobile($member['mobile'])) {
        $errors[] = "Member {$i}: {$err}";
    }
    if ($err = validateGender($member['gender'])) {
        $errors[] = "Member {$i}: {$err}";
    }

    $member['mobile'] = normalizeMobile($member['mobile']);
    $members[] = $member;
}

// Verify exactly 3 members and 1 leader
$leaderCount = array_sum(array_column($members, 'is_leader'));
if (count($members) !== 3) {
    $errors[] = 'Exactly 3 team members are required.';
}
if ($leaderCount !== 1) {
    $errors[] = 'Exactly one team leader is required.';
}

// Check duplicate PRNs within team
$prns = array_column($members, 'prn');
if (count($prns) !== count(array_unique($prns))) {
    $errors[] = 'Duplicate PRN detected within the team.';
}

// Check duplicate emails within team
$emails = array_map('strtolower', array_column($members, 'email'));
if (count($emails) !== count(array_unique($emails))) {
    $errors[] = 'Duplicate email detected within the team.';
}

// Confirmation checkbox
if (empty($_POST['confirm'])) {
    $errors[] = 'You must confirm that all information is correct.';
}

// Payment screenshot
if (empty($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Payment screenshot is required.';
} else {
    if ($err = validateUploadedImage($_FILES['payment_screenshot'])) {
        $errors[] = $err;
    }
}

if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['registration_form'] = $_POST;
    header('Location: ../../registration.php');
    exit;
}

try {
    $db = getDB();
    $settings = getSiteSettings();
    $fee = calculateRegistrationFee($settings);

    // Check for duplicate PRNs across existing registrations
    foreach ($members as $member) {
        $stmt = $db->prepare('SELECT tm.id FROM team_members tm WHERE tm.prn = ? LIMIT 1');
        $stmt->execute([$member['prn']]);
        if ($stmt->fetch()) {
            $_SESSION['registration_errors'] = ["PRN {$member['prn']} is already registered."];
            $_SESSION['registration_form'] = $_POST;
            header('Location: ../../registration.php');
            exit;
        }
    }

    $db->beginTransaction();

    $registrationId = generateRegistrationId($db);
    $screenshotPath = saveUploadedPayment($_FILES['payment_screenshot']);

    $stmt = $db->prepare('
        INSERT INTO teams (registration_id, team_name, registration_fee, payment_screenshot, payment_status, registration_status)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $registrationId,
        $teamName,
        $fee,
        $screenshotPath,
        'PENDING',
        'PENDING',
    ]);

    $teamId = (int) $db->lastInsertId();

    $memberStmt = $db->prepare('
        INSERT INTO team_members (team_id, full_name, prn, email, mobile, gender, is_leader)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($members as $member) {
        $memberStmt->execute([
            $teamId,
            $member['full_name'],
            $member['prn'],
            $member['email'],
            $member['mobile'],
            $member['gender'],
            $member['is_leader'],
        ]);
    }

    $db->commit();

    $_SESSION['registration_success'] = [
        'registration_id'     => $registrationId,
        'team_name'           => $teamName,
        'fee'                 => $fee,
        'leader_name'         => $members[0]['full_name'],
        'member_count'        => 3,
        'payment_status'      => 'PENDING',
        'registration_status' => 'PENDING',
    ];

    header('Location: ../../success.php?id=' . urlencode($registrationId));
    exit;

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['registration_errors'] = ['Registration failed. Please try again later.'];
    $_SESSION['registration_form'] = $_POST;
    header('Location: ../../registration.php');
    exit;
}
