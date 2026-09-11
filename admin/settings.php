<?php
require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();
$error = '';
$success = '';
$db = getDB();
$settings = getSiteSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $fields = [
            'event_name' => trim($_POST['event_name'] ?? ''),
            'event_date' => $_POST['event_date'] ?? '',
            'problem_release_date' => $_POST['problem_release_date'] ?? '',
            'early_bird_fee' => (float) ($_POST['early_bird_fee'] ?? 0),
            'early_bird_deadline' => $_POST['early_bird_deadline'] ?? '',
            'regular_fee' => (float) ($_POST['regular_fee'] ?? 0),
            'upi_id' => trim($_POST['upi_id'] ?? ''),
            'official_email' => trim($_POST['official_email'] ?? ''),
            'official_phone' => trim($_POST['official_phone'] ?? ''),
        ];
        $mediaFields = [
            'qr_image' => 'QR code image',
            'poster_image' => 'event poster',
        ];
        $uploadedMedia = [];
        if (!$fields['event_name'] || !$fields['event_date'] || !$fields['problem_release_date']) {
            $error = 'Event name and dates are required.';
        } elseif ($fields['early_bird_fee'] < 0 || $fields['regular_fee'] < 0) {
            $error = 'Fees cannot be negative.';
        } else {
            foreach ($mediaFields as $field => $label) {
                if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if ($uploadError = validateUploadedImage($_FILES[$field], $label)) {
                    $error = $uploadError;
                    break;
                }
            }

            if (!$error) {
                try {
                    foreach ($mediaFields as $field => $label) {
                        if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                            $uploadedMedia[$field] = saveUploadedSiteImage($_FILES[$field]);
                        }
                    }

                    $qrImage = $uploadedMedia['qr_image'] ?? $settings['qr_image'];
                    $posterImage = $uploadedMedia['poster_image'] ?? $settings['poster_image'];
                    $stmt = $db->prepare('UPDATE site_settings SET event_name = ?, event_date = ?, problem_release_date = ?, early_bird_fee = ?, early_bird_deadline = ?, regular_fee = ?, upi_id = ?, qr_image = ?, poster_image = ?, official_email = ?, official_phone = ? WHERE id = (SELECT id FROM (SELECT id FROM site_settings ORDER BY id ASC LIMIT 1) AS first_setting)');
                    $stmt->execute([
                        $fields['event_name'], $fields['event_date'], $fields['problem_release_date'],
                        $fields['early_bird_fee'], $fields['early_bird_deadline'], $fields['regular_fee'],
                        $fields['upi_id'], $qrImage, $posterImage, $fields['official_email'], $fields['official_phone'],
                    ]);

                    foreach ($mediaFields as $field => $label) {
                        if (isset($uploadedMedia[$field])) {
                            deleteUploadedSiteImage($settings[$field]);
                        }
                    }
                    $settings = getSiteSettings();
                    $success = 'Settings saved.';
                } catch (RuntimeException $exception) {
                    foreach ($uploadedMedia as $path) {
                        deleteUploadedSiteImage($path);
                    }
                    $error = $exception->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-main">
    <div class="admin-header"><h1>Settings</h1></div>
    <?php if ($error): ?><div class="alert alert-error"><?= sanitizeOutput($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert alert-success"><?= sanitizeOutput($success) ?></div><?php endif; ?>
    <div class="admin-card"><div style="padding:24px;"><form class="settings-form" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="settings-section"><h3>Event</h3>
            <div class="form-group"><label for="event_name">Event name</label><input class="form-control" id="event_name" name="event_name" required value="<?= sanitizeOutput($settings['event_name']) ?>"></div>
            <div class="form-group"><label for="event_date">Event date</label><input class="form-control" type="date" id="event_date" name="event_date" required value="<?= sanitizeOutput($settings['event_date']) ?>"></div>
            <div class="form-group"><label for="problem_release_date">Problem release date</label><input class="form-control" type="date" id="problem_release_date" name="problem_release_date" required value="<?= sanitizeOutput($settings['problem_release_date']) ?>"></div>
        </div>
        <div class="settings-section"><h3>Registration Fees</h3>
            <div class="form-group"><label for="early_bird_fee">Early bird fee</label><input class="form-control" type="number" min="0" step="0.01" id="early_bird_fee" name="early_bird_fee" required value="<?= sanitizeOutput((string) $settings['early_bird_fee']) ?>"></div>
            <div class="form-group"><label for="early_bird_deadline">Early bird deadline</label><input class="form-control" type="date" id="early_bird_deadline" name="early_bird_deadline" required value="<?= sanitizeOutput($settings['early_bird_deadline']) ?>"></div>
            <div class="form-group"><label for="regular_fee">Regular fee</label><input class="form-control" type="number" min="0" step="0.01" id="regular_fee" name="regular_fee" required value="<?= sanitizeOutput((string) $settings['regular_fee']) ?>"></div>
        </div>
        <div class="settings-section"><h3>Contact and Payment</h3>
            <div class="form-group"><label for="upi_id">UPI ID</label><input class="form-control" id="upi_id" name="upi_id" value="<?= sanitizeOutput($settings['upi_id']) ?>"></div>
            <div class="form-group"><label for="qr_image">Payment QR code</label><input class="form-control" type="file" id="qr_image" name="qr_image" accept="image/jpeg,image/jpg,image/png"><small>JPG, JPEG, or PNG. Maximum 5 MB.</small><?php if (!empty($settings['qr_image'])): ?><img class="settings-media-preview" src="../<?= sanitizeOutput($settings['qr_image']) ?>" alt="Current payment QR code"><?php endif; ?></div>
            <div class="form-group"><label for="poster_image">Event poster</label><input class="form-control" type="file" id="poster_image" name="poster_image" accept="image/jpeg,image/jpg,image/png"><small>JPG, JPEG, or PNG. Maximum 5 MB.</small><?php if (!empty($settings['poster_image'])): ?><img class="settings-media-preview" src="../<?= sanitizeOutput($settings['poster_image']) ?>" alt="Current event poster"><?php endif; ?></div>
            <div class="form-group"><label for="official_email">Official email</label><input class="form-control" type="email" id="official_email" name="official_email" value="<?= sanitizeOutput($settings['official_email']) ?>"></div>
            <div class="form-group"><label for="official_phone">Official phone</label><input class="form-control" id="official_phone" name="official_phone" value="<?= sanitizeOutput($settings['official_phone']) ?>"></div>
        </div>
        <button class="btn btn-primary" type="submit">Save Settings</button>
    </form></div></div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>