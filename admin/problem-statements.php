<?php
require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();
$error = '';
$success = '';
$db = getDB();
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
                if (!$title || !$description) {
                    throw new RuntimeException('Title and description are required.');
                }
                if ((int) ($_POST['id'] ?? 0) > 0) {
                    $stmt = $db->prepare('UPDATE problem_statements SET title = ?, description = ?, status = ? WHERE id = ?');
                    $stmt->execute([$title, $description, $status, (int) $_POST['id']]);
                } else {
                    $stmt = $db->prepare('INSERT INTO problem_statements (title, description, status) VALUES (?, ?, ?)');
                    $stmt->execute([$title, $description, $status]);
                }
                $success = 'Problem statement saved.';
                $editId = 0;
            } elseif ($action === 'delete') {
                $stmt = $db->prepare('DELETE FROM problem_statements WHERE id = ?');
                $stmt->execute([(int) $_POST['id']]);
                $success = 'Problem statement deleted.';
            } elseif ($action === 'publish-all') {
                $stmt = $db->prepare('UPDATE site_settings SET problem_statements_published = ? WHERE id = (SELECT id FROM (SELECT id FROM site_settings ORDER BY id ASC LIMIT 1) AS first_setting)');
                $stmt->execute([!empty($_POST['published']) ? 1 : 0]);
                $success = 'Public problem statement visibility updated.';
            }
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$editing = null;
if ($editId > 0) {
    $stmt = $db->prepare('SELECT * FROM problem_statements WHERE id = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}
$statements = $db->query('SELECT * FROM problem_statements ORDER BY created_at DESC')->fetchAll();
$settings = getSiteSettings();
$pageTitle = 'Problem Statements';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-main">
    <div class="admin-header"><h1>Problem Statements</h1></div>
    <?php if ($error): ?><div class="alert alert-error"><?= sanitizeOutput($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert alert-success"><?= sanitizeOutput($success) ?></div><?php endif; ?>
    <div class="admin-card problem-form"><div class="admin-card-header"><h2><?= $editing ? 'Edit Statement' : 'Add Statement' ?></h2></div><div style="padding:24px;">
        <form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
            <div class="form-group"><label for="title">Title</label><input class="form-control" id="title" name="title" required value="<?= sanitizeOutput($editing['title'] ?? '') ?>"></div>
            <div class="form-group"><label for="description">Description</label><textarea class="form-control" id="description" name="description" rows="5" required><?= sanitizeOutput($editing['description'] ?? '') ?></textarea></div>
            <div class="form-group"><label for="status">Status</label><select class="form-control" id="status" name="status"><option value="draft" <?= ($editing['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= ($editing['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option></select></div>
            <button class="btn btn-primary" type="submit">Save Statement</button><?php if ($editing): ?> <a class="btn btn-outline" href="problem-statements.php">Cancel</a><?php endif; ?>
        </form>
    </div></div>
    <div class="admin-card"><div class="admin-card-header"><h2>Visibility</h2><form method="POST" class="toggle-switch"><?= csrfField() ?><input type="hidden" name="action" value="publish-all"><input type="checkbox" name="published" value="1" onchange="this.form.submit()" <?= !empty($settings['problem_statements_published']) ? 'checked' : '' ?>><span>Show published statements on the public site</span></form></div>
        <?php foreach ($statements as $statement): ?><div class="problem-list-item"><div><h4><?= sanitizeOutput($statement['title']) ?></h4><p><?= sanitizeOutput(mb_strimwidth($statement['description'], 0, 160, '...')) ?></p></div><div class="actions"><span class="badge <?= $statement['status'] === 'published' ? 'badge-success' : 'badge-pending' ?>"><?= sanitizeOutput($statement['status']) ?></span><a class="btn btn-outline btn-sm" href="?edit=<?= (int) $statement['id'] ?>">Edit</a><form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $statement['id'] ?>"><button class="btn btn-reject btn-sm" type="submit">Delete</button></form></div></div><?php endforeach; ?>
        <?php if (!$statements): ?><p style="padding:24px;color:var(--text-muted);">No problem statements created.</p><?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>