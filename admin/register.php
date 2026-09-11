<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/php/helpers/functions.php';
require_once dirname(__DIR__) . '/php/helpers/security.php';

initSession();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {

            try {
                $db = getDB();

                // Check if username or email already exists
                $stmt = $db->prepare(
                    'SELECT id FROM admins WHERE username = ? OR email = ? LIMIT 1'
                );
                $stmt->execute([$username, $email]);

                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {

                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert admin
                    $stmt = $db->prepare(
                        'INSERT INTO admins (username, email, password, created_at)
                         VALUES (?, ?, ?, NOW())'
                    );

                    $stmt->execute([
                        $username,
                        $email,
                        $hashedPassword
                    ]);

                    $success = 'Registration successful! You can now login.';
                }

            } catch (Exception $e) {
                error_log('Registration error: ' . $e->getMessage());
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Registration | AI Extreme 2026</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<div class="admin-login">

    <div class="login-card">

        <h1>AI EXTREME 2026</h1>

        <p class="subtitle">Create Admin Account</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= sanitizeOutput($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= sanitizeOutput($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <?= csrfField() ?>

            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    minlength="8"
                    required
                >
            </div>

            <button
                type="submit"
                class="btn btn-primary"
                style="width:100%;"
            >
                Register Admin
            </button>

        </form>

        <p style="text-align:center; margin-top:15px;">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>
</html>
