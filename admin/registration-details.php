<?php
require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();
$registrationId = trim($_GET['id'] ?? '');
$error = '';
$success = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif (!in_array($_POST['action'] ?? '', ['verify', 'reject-payment', 'approve', 'reject-registration'], true)) {
        $error = 'Invalid action.';
    } else {
        $action = $_POST['action'];
        $reason = trim($_POST['reason'] ?? '');
        $updates = match ($action) {
            'verify' => ['payment_status' => 'VERIFIED', 'payment_rejection_reason' => null],
            'reject-payment' => ['payment_status' => 'REJECTED', 'payment_rejection_reason' => $reason ?: 'Payment was rejected.'],
            'approve' => ['registration_status' => 'APPROVED', 'registration_rejection_reason' => null],
            'reject-registration' => ['registration_status' => 'REJECTED', 'registration_rejection_reason' => $reason ?: 'Registration was rejected.'],
        };
        $set = implode(', ', array_map(static fn ($field) => "$field = ?", array_keys($updates)));
        $values = array_values($updates);
        $values[] = $registrationId;
        $stmt = $db->prepare("UPDATE teams SET $set WHERE registration_id = ?");
        $stmt->execute($values);
        $success = 'Registration updated.';
    }
}

$team = getTeamByRegistrationId($registrationId);
if (!$team) {
    http_response_code(404);
    $error = 'Registration not found.';
    $members = [];
} else {
    $members = getTeamMembers((int) $team['id']);
}
$pageTitle = 'Registration Details';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-main">
    <div class="admin-header"><h1>Registration Details</h1><a class="btn btn-outline btn-sm" href="registrations.php">Back to Registrations</a></div>
    <?php if ($error): ?><div class="alert alert-error"><?= sanitizeOutput($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= sanitizeOutput($success) ?></div><?php endif; ?>
    <?php if ($team): ?>
        <div class="detail-grid">
            <section class="detail-section"><h3>Team</h3>
                <div class="detail-row"><span class="label">Registration ID</span><span class="value"><?= sanitizeOutput($team['registration_id']) ?></span></div>
                <div class="detail-row"><span class="label">Team name</span><span class="value"><?= sanitizeOutput($team['team_name']) ?></span></div>
                <div class="detail-row"><span class="label">Fee</span><span class="value"><?= formatCurrency((float) $team['registration_fee']) ?></span></div>
                <div class="detail-row"><span class="label">Registered</span><span class="value"><?= formatDate($team['created_at'], 'd M Y, H:i') ?></span></div>
            </section>
            <section class="detail-section"><h3>Status</h3>
                <div class="detail-row"><span class="label">Payment</span><span class="value badge <?= statusBadgeClass($team['payment_status']) ?>"><?= sanitizeOutput($team['payment_status']) ?></span></div>
                <div class="detail-row"><span class="label">Registration</span><span class="value badge <?= statusBadgeClass($team['registration_status']) ?>"><?= sanitizeOutput($team['registration_status']) ?></span></div>
                <?php if ($team['payment_screenshot']): ?><img class="screenshot-preview" src="../<?= sanitizeOutput($team['payment_screenshot']) ?>" alt="Payment screenshot" style="margin-top:16px;max-height:220px;"><?php endif; ?>
            </section>
        </div>
        <section class="detail-section"><h3>Participants</h3>
            <div class="table-responsive"><table class="admin-table"><thead><tr><th>Name</th><th>PRN</th><th>Email</th><th>Mobile</th><th>Role</th></tr></thead><tbody>
            <?php foreach ($members as $member): ?><tr><td><?= sanitizeOutput($member['full_name']) ?></td><td><?= sanitizeOutput($member['prn']) ?></td><td><?= sanitizeOutput($member['email']) ?></td><td><?= sanitizeOutput($member['mobile']) ?></td><td><?= $member['is_leader'] ? 'Leader' : 'Member' ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
            <div class="action-buttons">
                <?php if ($team['payment_status'] === 'PENDING'): ?><form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="verify"><button class="btn btn-verify" type="submit">Verify Payment</button></form><button class="btn btn-reject" type="button" data-toggle-rejection="payment-rejection">Reject Payment</button><?php endif; ?>
                <?php if ($team['registration_status'] === 'PENDING'): ?><form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="approve"><button class="btn btn-approve" type="submit">Approve Team</button></form><button class="btn btn-reject" type="button" data-toggle-rejection="registration-rejection">Reject Team</button><?php endif; ?>
            </div>
            <form id="payment-rejection" class="rejection-form" method="POST"><?= csrfField() ?><input type="hidden" name="action" value="reject-payment"><textarea name="reason" placeholder="Reason for rejecting payment"></textarea><button class="btn btn-reject" type="submit">Confirm Payment Rejection</button></form>
            <form id="registration-rejection" class="rejection-form" method="POST"><?= csrfField() ?><input type="hidden" name="action" value="reject-registration"><textarea name="reason" placeholder="Reason for rejecting registration"></textarea><button class="btn btn-reject" type="submit">Confirm Team Rejection</button></form>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>