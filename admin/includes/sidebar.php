<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>AI EXTREME 2026</h2>
        <p>Admin Dashboard</p>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a></li>
        <li><a href="registrations.php" class="<?= in_array($currentPage, ['registrations.php', 'registration-details.php']) ? 'active' : '' ?>">📋 Registrations</a></li>
        <li>
            <a href="messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">
                ✉️ Messages
                <?php if (($stats['unreadMessages'] ?? 0) > 0): ?>
                    <span class="badge-count"><?= (int)$stats['unreadMessages'] ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="problem-statements.php" class="<?= $currentPage === 'problem-statements.php' ? 'active' : '' ?>">🎯 Problem Statements</a></li>
        <li><a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">⚙️ Settings</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php">🚪 Logout</a>
    </div>
</aside>
