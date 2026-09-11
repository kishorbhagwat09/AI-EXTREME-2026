<?php
/**
 * Admin Setup Script
 * Run once to create the first admin account, then DELETE this file.
 *
 * Usage: php setup/create-admin.php
 * Or visit: http://localhost/AI-EXTREME-2026/setup/create-admin.php
 */

require_once __DIR__ . '/../php/helpers/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT id FROM admins WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Admin with this username or email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $db->prepare('INSERT INTO admins (username, email, password) VALUES (?, ?, ?)');
                $insert->execute([$username, $email, $hash]);
                $message = 'Admin account created successfully! Please delete this setup script immediately.';
            }
        } catch (Exception $e) {
            $error = 'Setup failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - AI Extreme 2026</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 60px auto; padding: 20px; background: #0a0e1a; color: #e0e6f0; }
        h1 { color: #00d4ff; font-size: 1.5rem; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .alert-success { background: rgba(0,200,100,0.15); border: 1px solid #00c864; }
        .alert-error { background: rgba(255,60,60,0.15); border: 1px solid #ff3c3c; }
        label { display: block; margin-bottom: 4px; font-size: 0.875rem; color: #8899aa; }
        input { width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #334; border-radius: 6px; background: #121828; color: #e0e6f0; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #0066ff, #00d4ff); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .warning { background: rgba(255,165,0,0.1); border: 1px solid #ffa500; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; }
    </style>
</head>
<body>
    <h1>AI Extreme 2026 — Admin Setup</h1>
    <div class="warning">⚠ Delete this file after creating your admin account.</div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$message): ?>
    <form method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required minlength="3">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

        <button type="submit">Create Admin Account</button>
    </form>
    <?php endif; ?>
</body>
</html>
