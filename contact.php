<?php
require_once __DIR__ . '/php/helpers/functions.php';
require_once __DIR__ . '/php/helpers/security.php';

initSession();
$settings = getSiteSettings();
$success = $_SESSION['contact_success'] ?? null;
$error = $_SESSION['contact_error'] ?? null;
$errors = $_SESSION['contact_errors'] ?? [];
$form = $_SESSION['contact_form'] ?? [];
unset($_SESSION['contact_success'], $_SESSION['contact_error'], $_SESSION['contact_errors'], $_SESSION['contact_form']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | AI Extreme 2026</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <nav class="navbar scrolled">
        <div class="container">
            <a href="index.html" class="navbar-brand">
                <img src="logo/DCAC_LOGO.jpeg" alt="DCAC Logo" width="44" height="44">
                <div class="brand-text">
                    <span class="brand-name">SANJIVANI</span>
                    <span class="brand-sub">UNIVERSITY</span>
                </div>
            </a>
            <ul class="navbar-nav">
                <li><a href="index.html">Home</a></li>
                <li><a href="registration.php">Register</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
            <div class="navbar-actions">
                <a href="registration.php" class="btn btn-primary btn-sm">Register Your Team</a>
                <button class="hamburger" aria-label="Toggle menu"><span></span><span></span><span></span></button>
            </div>
        </div>
    </nav>
    <div class="mobile-menu">
        <a href="index.html">Home</a>
        <a href="registration.php">Register</a>
        <a href="contact.php">Contact</a>
    </div>

    <main class="contact-page">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    <div class="contact-info-block">
                        <h3>Organizer</h3>
                        <p>Department of Computer Applications<br>Sanjivani University</p>
                    </div>
                    <div class="contact-info-block">
                        <h3>Address</h3>
                        <p>Sanjivani University Campus<br>Kopargaon, Maharashtra</p>
                    </div>
                    <div class="contact-info-block">
                        <h3>Email</h3>
                        <p><a href="mailto:<?= sanitizeOutput($settings['official_email']) ?>"><?= sanitizeOutput($settings['official_email']) ?></a></p>
                    </div>
                    <div class="contact-info-block">
                        <h3>Phone</h3>
                        <p><a href="tel:<?= sanitizeOutput($settings['official_phone']) ?>"><?= sanitizeOutput($settings['official_phone']) ?></a></p>
                    </div>
                </div>

                <div class="glass-card contact-form-card">
                    <h2>Send a Message</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= sanitizeOutput($success) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= sanitizeOutput($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul style="margin:0;padding-left:20px;">
                                <?php foreach ($errors as $e): ?><li><?= sanitizeOutput($e) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="php/contact/process-contact.php" method="POST" novalidate>
                        <?= csrfField() ?>
                        <div class="form-group">
                            <label for="name">Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required value="<?= sanitizeOutput($form['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= sanitizeOutput($form['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" required value="<?= sanitizeOutput($form['subject'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" class="form-control" required><?= sanitizeOutput($form['message'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-bottom">
            <p>&copy; 2026 Department of Computer Applications, Sanjivani University.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
