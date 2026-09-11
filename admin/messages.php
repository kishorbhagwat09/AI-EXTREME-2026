<?php
require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();
$error = '';
$success = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif (!empty($_POST['message_id']) && in_array($_POST['action'] ?? '', ['read', 'delete'], true)) {
        $messageId = (int) $_POST['message_id'];
        if ($_POST['action'] === 'read') {
            $stmt = $db->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?');
            $stmt->execute([$messageId]);
            $success = 'Message marked as read.';
        } else {
            $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = ?');
            $stmt->execute([$messageId]);
            $success = 'Message deleted.';
        }
    }
}

$messages = $db->query('SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC')->fetchAll();
$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-main">
    <div class="admin-header"><h1>Messages</h1><span class="admin-user"><?= count(array_filter($messages, static fn ($message) => !$message['is_read'])) ?> unread</span></div>
    <?php if ($error): ?><div class="alert alert-error"><?= sanitizeOutput($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= sanitizeOutput($success) ?></div><?php endif; ?>
    <div class="admin-card"><div class="table-responsive"><table class="admin-table">
        <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$messages): ?><tr><td colspan="5" style="text-align:center;color:var(--text-muted);">No messages yet.</td></tr><?php endif; ?>
        <?php foreach ($messages as $message): ?><tr>
            <td><strong><?= sanitizeOutput($message['name']) ?></strong><br><small><?= sanitizeOutput($message['email']) ?></small></td>
            <td><?= sanitizeOutput($message['subject']) ?><?= !$message['is_read'] ? ' <span class="badge badge-pending">New</span>' : '' ?></td>
            <td><?= nl2br(sanitizeOutput($message['message'])) ?></td><td><?= formatDate($message['created_at'], 'd M Y, H:i') ?></td>
            <td class="actions"><form method="POST"><?= csrfField() ?><input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>"><input type="hidden" name="action" value="<?= $message['is_read'] ? 'delete' : 'read' ?>"><button class="btn btn-outline btn-sm" type="submit"><?= $message['is_read'] ? 'Delete' : 'Mark read' ?></button></form><?php if (!$message['is_read']): ?><form method="POST"><?= csrfField() ?><input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-reject btn-sm" type="submit">Delete</button></form><?php endif; ?></td>
        </tr><?php endforeach; ?>
        </tbody>
    </table></div></div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>