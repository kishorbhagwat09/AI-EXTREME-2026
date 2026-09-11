<?php
require_once __DIR__ . '/php/helpers/functions.php';
require_once __DIR__ . '/php/helpers/security.php';

initSession();
$settings = getSiteSettings();
$fee = calculateRegistrationFee($settings);
$earlyBird = isEarlyBird($settings);
$errors = $_SESSION['registration_errors'] ?? [];
$form = $_SESSION['registration_form'] ?? [];
unset($_SESSION['registration_errors'], $_SESSION['registration_form']);

function old(string $key, array $form): string {
    return sanitizeOutput($form[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | AI Extreme 2026</title>
    <meta name="description" content="Register your team for AI Extreme 2026 - Sanjivani University">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/registration.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <nav class="navbar scrolled">
        <div class="container">
            <a href="index.html" class="navbar-brand">
                <img src="assets/images/university-logo.png" alt="Sanjivani University Logo" width="44" height="44">
                <div class="brand-text">
                    <span class="brand-name">SANJIVANI</span>
                    <span class="brand-sub">UNIVERSITY</span>
                </div>
            </a>
            <ul class="navbar-nav">
                <li><a href="index.html">Home</a></li>
                <li><a href="registration.php" class="active">Register</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="navbar-actions">
                <button class="hamburger" aria-label="Toggle menu"><span></span><span></span><span></span></button>
            </div>
        </div>
    </nav>
    <div class="mobile-menu">
        <a href="index.html">Home</a>
        <a href="registration.php">Register</a>
        <a href="contact.php">Contact</a>
    </div>

    <header class="page-header">
        <div class="container">
            <h1>Team Registration</h1>
            <p>Register your 3-member team for AI Extreme 2026</p>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitizeOutput($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="registration-progress">
            <div class="progress-step active" data-step="1">
                <div class="progress-step-circle">1</div>
                <div class="progress-step-label">Team Info</div>
            </div>
            <div class="progress-step" data-step="2">
                <div class="progress-step-circle">2</div>
                <div class="progress-step-label">Members</div>
            </div>
            <div class="progress-step" data-step="3">
                <div class="progress-step-circle">3</div>
                <div class="progress-step-label">Payment</div>
            </div>
            <div class="progress-step" data-step="4">
                <div class="progress-step-circle">4</div>
                <div class="progress-step-label">Confirm</div>
            </div>
        </div>

        <form id="registration-form" class="registration-form" action="php/registration/process-registration.php" method="POST" enctype="multipart/form-data" novalidate>
            <?= csrfField() ?>

            <!-- Step 1: Team Information -->
            <div class="form-step active" id="step-1">
                <h2 class="form-step-title">Step 1 — Team Information</h2>
                <div class="glass-card">
                    <div class="form-group">
                        <label for="team_name">Team Name <span class="required">*</span></label>
                        <input type="text" id="team_name" name="team_name" class="form-control" required minlength="3" maxlength="100" value="<?= old('team_name', $form) ?>" placeholder="Enter your team name">
                    </div>
                </div>
            </div>

            <!-- Step 2: Team Members -->
            <div class="form-step" id="step-2">
                <h2 class="form-step-title">Step 2 — Team Members</h2>

                <?php
                $members = [
                    ['prefix' => 'leader', 'label' => 'Team Leader', 'leader' => true],
                    ['prefix' => 'member2', 'label' => 'Team Member 2', 'leader' => false],
                    ['prefix' => 'member3', 'label' => 'Team Member 3', 'leader' => false],
                ];
                foreach ($members as $m):
                ?>
                <div class="member-section <?= $m['leader'] ? 'leader' : '' ?>">
                    <h3><?= $m['label'] ?></h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="<?= $m['prefix'] ?>_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="<?= $m['prefix'] ?>_name" name="<?= $m['prefix'] ?>_name" class="form-control" required value="<?= old($m['prefix'] . '_name', $form) ?>">
                        </div>
                        <div class="form-group">
                            <label for="<?= $m['prefix'] ?>_prn">PRN <span class="required">*</span></label>
                            <input type="text" id="<?= $m['prefix'] ?>_prn" name="<?= $m['prefix'] ?>_prn" class="form-control" required value="<?= old($m['prefix'] . '_prn', $form) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="<?= $m['prefix'] ?>_email">Email <span class="required">*</span></label>
                            <input type="email" id="<?= $m['prefix'] ?>_email" name="<?= $m['prefix'] ?>_email" class="form-control" required value="<?= old($m['prefix'] . '_email', $form) ?>">
                        </div>
                        <div class="form-group">
                            <label for="<?= $m['prefix'] ?>_mobile">Mobile Number <span class="required">*</span></label>
                            <input type="tel" id="<?= $m['prefix'] ?>_mobile" name="<?= $m['prefix'] ?>_mobile" class="form-control" required placeholder="10-digit number" value="<?= old($m['prefix'] . '_mobile', $form) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="<?= $m['prefix'] ?>_gender">Gender <span class="required">*</span></label>
                        <select id="<?= $m['prefix'] ?>_gender" name="<?= $m['prefix'] ?>_gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <?php foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $g): ?>
                                <option value="<?= $g ?>" <?= (old($m['prefix'] . '_gender', $form) === $g) ? 'selected' : '' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="team-summary">
                    <h4>Team Summary</h4>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="label">Total Members</div>
                            <div class="value" id="summary-total">3</div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Team Leader</div>
                            <div class="value" id="summary-leader" style="font-size:0.85rem;">-</div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Boys</div>
                            <div class="value" id="summary-boys">0</div>
                        </div>
                        <div class="summary-item">
                            <div class="label">Girls</div>
                            <div class="value" id="summary-girls">0</div>
                        </div>
                    </div>
                    <input type="hidden" id="summary-other" value="0">
                </div>
            </div>

            <!-- Step 3: Payment -->
            <div class="form-step" id="step-3">
                <h2 class="form-step-title">Step 3 — Payment</h2>
                <div class="glass-card payment-section">
                    <h3>Registration Payment</h3>
                    <p>Current Fee: <strong class="current-fee text-cyan"><?= formatCurrency($fee) ?></strong></p>

                    <div class="fee-options">
                        <div class="fee-option <?= $earlyBird ? 'active' : '' ?>" id="fee-early-bird">
                            <h4>Early Bird Registration</h4>
                            <div class="amount">₹<?= (int)$settings['early_bird_fee'] ?></div>
                            <div class="deadline">Till <?= formatDate($settings['early_bird_deadline']) ?></div>
                        </div>
                        <div class="fee-option <?= !$earlyBird ? 'active' : '' ?>" id="fee-regular">
                            <h4>Regular Registration</h4>
                            <div class="amount">₹<?= (int)$settings['regular_fee'] ?></div>
                            <div class="deadline">After <?= formatDate($settings['early_bird_deadline']) ?></div>
                        </div>
                    </div>

                    <div class="qr-container">
                        <img src="<?= sanitizeOutput($settings['qr_image']) ?>" alt="UPI Payment QR Code">
                    </div>
                    <p class="upi-id">UPI ID: <?= sanitizeOutput($settings['upi_id']) ?></p>

                    <div class="payment-instructions">
                        <ol>
                            <li>Scan the QR code using your UPI application.</li>
                            <li>Pay the displayed registration amount.</li>
                            <li>Complete the payment.</li>
                            <li>Take a screenshot of the successful transaction.</li>
                            <li>Upload the screenshot below.</li>
                            <li>Submit your registration.</li>
                        </ol>
                    </div>

                    <div class="file-upload" id="file-upload">
                        <input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/jpeg,image/jpg,image/png" required>
                        <div class="file-upload-icon">📷</div>
                        <p><strong>Upload Payment Screenshot</strong></p>
                        <p>JPG, JPEG, PNG — Max 5 MB</p>
                    </div>
                    <div class="file-preview" id="file-preview"></div>
                </div>
            </div>

            <!-- Step 4: Confirmation -->
            <div class="form-step" id="step-4">
                <h2 class="form-step-title">Step 4 — Confirmation</h2>
                <div class="glass-card confirmation-summary">
                    <h3>Registration Summary</h3>
                    <div class="confirmation-row"><span class="label">Team Name</span><span class="value" id="confirm-team-name">-</span></div>
                    <div class="confirmation-row"><span class="label">Team Leader</span><span class="value" id="confirm-leader">-</span></div>
                    <div class="confirmation-row"><span class="label">Member 2</span><span class="value" id="confirm-member2">-</span></div>
                    <div class="confirmation-row"><span class="label">Member 3</span><span class="value" id="confirm-member3">-</span></div>
                    <div class="confirmation-row"><span class="label">Total Members</span><span class="value">3</span></div>
                    <div class="confirmation-row"><span class="label">Registration Fee</span><span class="value" id="confirm-fee"><?= formatCurrency($fee) ?></span></div>
                    <div class="confirmation-row"><span class="label">Payment Screenshot</span><span class="value" id="confirm-screenshot">Not uploaded</span></div>

                    <div class="checkbox-group mt-3">
                        <input type="checkbox" id="confirm" name="confirm" value="1" required>
                        <label for="confirm">I confirm that all information provided by my team is correct.</label>
                    </div>
                </div>
            </div>

            <div class="form-navigation">
                <button type="button" id="btn-back" class="btn btn-outline" style="display:none;">Back</button>
                <button type="button" id="btn-next" class="btn btn-primary">Continue</button>
                <button type="submit" id="btn-submit" class="btn btn-primary" style="display:none;">Complete Registration</button>
            </div>
        </form>
    </main>

    <footer class="footer">
        <div class="container footer-bottom">
            <p>&copy; 2026 Department of Computer Applications, Sanjivani University.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/registration.js"></script>
</body>
</html>
