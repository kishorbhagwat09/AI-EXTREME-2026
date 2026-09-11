<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$stats = getDashboardStats();
$db = getDB();
$recentTeams = $db->query('
    SELECT t.*, 
        (SELECT full_name FROM team_members WHERE team_id = t.id AND is_leader = 1 LIMIT 1) as leader_name
    FROM teams t ORDER BY t.created_at DESC LIMIT 10
')->fetchAll();
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Dashboard</h1>
        <span class="admin-user">Welcome, <?= sanitizeOutput($adminName) ?></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card highlight">
            <div class="stat-label">Total Teams</div>
            <div class="stat-value"><?= $stats['totalTeams'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Participants</div>
            <div class="stat-value"><?= $stats['totalParticipants'] ?></div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value"><?= $stats['pendingPayments'] ?></div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Verified Payments</div>
            <div class="stat-value"><?= $stats['verifiedPayments'] ?></div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">Rejected Payments</div>
            <div class="stat-value"><?= $stats['rejectedPayments'] ?></div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Approved Teams</div>
            <div class="stat-value"><?= $stats['approvedTeams'] ?></div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Registrations</h2>
            <a href="registrations.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Team Name</th>
                        <th>Leader</th>
                        <th>Fee</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTeams)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">No registrations yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTeams as $team): ?>
                        <tr>
                            <td><?= sanitizeOutput($team['registration_id']) ?></td>
                            <td><?= sanitizeOutput($team['team_name']) ?></td>
                            <td><?= sanitizeOutput($team['leader_name']) ?></td>
                            <td><?= formatCurrency((float)$team['registration_fee']) ?></td>
                            <td><span class="badge <?= statusBadgeClass($team['payment_status']) ?>"><?= sanitizeOutput($team['payment_status']) ?></span></td>
                            <td><span class="badge <?= statusBadgeClass($team['registration_status']) ?>"><?= sanitizeOutput($team['registration_status']) ?></span></td>
                            <td><?= formatDate($team['created_at'], 'd M Y') ?></td>
                            <td><a href="registration-details.php?id=<?= urlencode($team['registration_id']) ?>" class="btn btn-outline btn-sm">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
