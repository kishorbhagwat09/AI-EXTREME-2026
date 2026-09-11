<?php
require_once __DIR__ . '/php/helpers/functions.php';
require_once __DIR__ . '/php/helpers/security.php';

initSession();

$registrationId = $_GET['id'] ?? '';
$data = $_SESSION['registration_success'] ?? null;

if (!$data && $registrationId) {
    $team = getTeamByRegistrationId($registrationId);
    if ($team) {
        $members = getTeamMembers($team['id']);
        $leader = null;
        foreach ($members as $m) {
            if ($m['is_leader']) { $leader = $m; break; }
        }
        $data = [
            'registration_id'     => $team['registration_id'],
            'team_name'           => $team['team_name'],
            'fee'                 => $team['registration_fee'],
            'leader_name'         => $leader['full_name'] ?? '-',
            'member_count'        => count($members),
            'payment_status'      => $team['payment_status'],
            'registration_status' => $team['registration_status'],
            'members'             => $members,
            'created_at'          => $team['created_at'],
        ];
    }
}

if (!$data) {
    header('Location: registration.php');
    exit;
}

unset($_SESSION['registration_success']);
$genderCounts = countGenderMembers($data['members'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful | AI Extreme 2026</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/registration.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <nav class="navbar scrolled no-print">
        <div class="container">
            <a href="index.html" class="navbar-brand">
                <img src="logo/DCAC_LOGO.jpeg" alt="DCAC Logo" width="44" height="44">
                <div class="brand-text">
                    <span class="brand-name">SANJIVANI</span>
                    <span class="brand-sub">UNIVERSITY</span>
                </div>
            </a>
        </div>
    </nav>

    <main class="success-page">
        <div class="container">
            <div class="glass-card success-card" id="receipt">
                <div class="success-icon no-print">✓</div>
                <h1>Registration Successful!</h1>
                <p>Congratulations! Your team has been successfully registered for:</p>
                <p><strong>AI EXTREME 2026</strong></p>

                <div class="registration-id-display"><?= sanitizeOutput($data['registration_id']) ?></div>

                <div class="success-details">
                    <div class="confirmation-row"><span class="label">Registration ID</span><span class="value"><?= sanitizeOutput($data['registration_id']) ?></span></div>
                    <div class="confirmation-row"><span class="label">Team Name</span><span class="value"><?= sanitizeOutput($data['team_name']) ?></span></div>
                    <div class="confirmation-row"><span class="label">Team Leader</span><span class="value"><?= sanitizeOutput($data['leader_name']) ?></span></div>
                    <div class="confirmation-row"><span class="label">Members</span><span class="value"><?= (int)$data['member_count'] ?></span></div>
                    <div class="confirmation-row"><span class="label">Registration Fee</span><span class="value"><?= formatCurrency((float)$data['fee']) ?></span></div>
                    <div class="confirmation-row"><span class="label">Payment Status</span><span class="value"><span class="badge badge-pending"><?= sanitizeOutput(ucfirst(strtolower($data['payment_status']))) ?> Verification</span></span></div>
                    <div class="confirmation-row"><span class="label">Registration Status</span><span class="value"><span class="badge badge-pending"><?= sanitizeOutput(ucfirst(strtolower($data['registration_status']))) ?></span></span></div>
                </div>

                <?php if (!empty($data['members'])): ?>
                <div class="success-details receipt-details">
                    <h3 style="font-size:0.85rem;margin-bottom:12px;color:var(--accent-cyan);">TEAM MEMBERS</h3>
                    <?php foreach ($data['members'] as $i => $member): ?>
                    <div class="confirmation-row">
                        <span class="label"><?= $member['is_leader'] ? 'Leader' : 'Member ' . ($i + 1) ?> — <?= sanitizeOutput($member['full_name']) ?></span>
                        <span class="value">PRN: <?= sanitizeOutput($member['prn']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="confirmation-row"><span class="label">Girls</span><span class="value"><?= $genderCounts['girls'] ?></span></div>
                    <div class="confirmation-row"><span class="label">Boys</span><span class="value"><?= $genderCounts['boys'] ?></span></div>
                    <?php if (!empty($data['created_at'])): ?>
                    <div class="confirmation-row"><span class="label">Registration Date</span><span class="value"><?= formatDate($data['created_at'], 'd M Y, h:i A') ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <p class="alert alert-info no-print">Please save your Registration ID for future communication.</p>

                <div class="success-actions no-print">
                    <button onclick="window.print()" class="btn btn-primary">Download Receipt</button>
                    <a href="index.html" class="btn btn-outline">Back to Home</a>
                </div>
            </div>
        </div>
    </main>

    <style>
        @media print {
            body { background: #fff; color: #111; }
            .navbar, .no-print { display: none !important; }
            .success-card { border: none; box-shadow: none; background: #fff; color: #111; }
            .success-details { background: #f5f5f5; border: 1px solid #ddd; }
            .confirmation-row { border-color: #ddd; color: #111; }
            .confirmation-row .label { color: #555; }
            .registration-id-display { color: #0066ff; }
            h1 { color: #00c864; }
        }
    </style>
</body>
</html>
