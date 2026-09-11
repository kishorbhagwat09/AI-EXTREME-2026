<?php
/**
 * Process contact form submission
 */

require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validation.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../contact.php');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['contact_error'] = 'Invalid security token. Please try again.';
    header('Location: ../../contact.php');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($err = validateContactName($name)) $errors[] = $err;
if ($err = validateEmail($email)) $errors[] = $err;
if ($err = validateContactSubject($subject)) $errors[] = $err;
if ($err = validateContactMessage($message)) $errors[] = $err;

if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_form'] = compact('name', 'email', 'subject', 'message');
    header('Location: ../../contact.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $subject, $message]);

    $_SESSION['contact_success'] = 'Your message has been received successfully.';
    header('Location: ../../contact.php');
    exit;

} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    $_SESSION['contact_error'] = 'Unable to send message. Please try again later.';
    header('Location: ../../contact.php');
    exit;
}
