<?php
require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();
$error = '';
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

try {
    $db = getDB();
    $query = '
        SELECT t.*, (SELECT full_name FROM team_members WHERE team_id = t.id AND is_leader = 1 LIMIT 1) AS leader_name
        FROM teams t
        WHERE (? = \'\' OR t.registration_id LIKE ? OR t.team_name LIKE ?)
        AND (? = \'\' OR t.registration_status = ?)
        ORDER BY t.created_at DESC';
    $stmt = $db->prepare($query);
    $statusFilter = in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true) ? $status : '';
    $stmt->execute([$search, '%' . $search . '%', '%' . $search . '%', $statusFilter, $statusFilter]);
    $registrations = $stmt->fetchAll();
} catch (Throwable $exception) {
    error_log('Registrations error: ' . $exception->getMessage());
    $error = 'Unable to load registrations.';
    $registrations = [];
}

$pageTitle = 'Registrations';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-main">
    <div class="admin-header"><h1>Registrations</h1><span class="admin-user"><?= count($registrations) ?> result<?= count($registrations) === 1 ? '' : 's' ?></span></div>
    <?php if ($error): ?><div class="alert alert-error"><?= sanitizeOutput($error) ?></div><?php endif; ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>All Teams</h2>
            <form class="admin-filters" method="GET">
                <input type="search" name="search" value="<?= sanitizeOutput($search) ?>" placeholder="Search ID or team">
                <select name="status"><option value="">All statuses</option><?php foreach (['PENDING', 'APPROVED', 'REJECTED'] as $option): ?><option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
                <button class="btn btn-outline btn-sm" type="submit">Filter</button>
            </form>
        </div>
        <div class="table-responsive"><table class="admin-table">
            <thead><tr><th>Registration ID</th><th>Team</th><th>Leader</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$registrations): ?><tr><td colspan="7" style="text-align:center;color:var(--text-muted);">No registrations found.</td></tr><?php endif; ?>
            <?php foreach ($registrations as $registration): ?><tr>
                <td><?= sanitizeOutput($registration['registration_id']) ?></td><td><?= sanitizeOutput($registration['team_name']) ?></td><td><?= sanitizeOutput($registration['leader_name']) ?></td>
                <td><span class="badge <?= statusBadgeClass($registration['payment_status']) ?>"><?= sanitizeOutput($registration['payment_status']) ?></span></td>
                <td><span class="badge <?= statusBadgeClass($registration['registration_status']) ?>"><?= sanitizeOutput($registration['registration_status']) ?></span></td><td><?= formatDate($registration['created_at']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="registration-details.php?id=<?= urlencode($registration['registration_id']) ?>">View</a></td>
            </tr><?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>