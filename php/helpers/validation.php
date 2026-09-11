<?php
/**
 * Input validation helpers
 */

function validateTeamName(string $name): ?string
{
    $name = trim($name);
    if (strlen($name) < 3) {
        return 'Team name must be at least 3 characters.';
    }
    if (strlen($name) > 100) {
        return 'Team name must not exceed 100 characters.';
    }
    if (preg_match('/<[^>]+>/', $name)) {
        return 'Team name contains invalid characters.';
    }
    return null;
}

function validateEmail(string $email): ?string
{
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    if (strlen($email) > 100) {
        return 'Email must not exceed 100 characters.';
    }
    return null;
}

function validateMobile(string $mobile): ?string
{
    $mobile = trim($mobile);
    $mobile = preg_replace('/^\+91/', '', $mobile);
    $mobile = preg_replace('/[\s\-]/', '', $mobile);

    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        return 'Please enter a valid 10-digit Indian mobile number.';
    }
    return null;
}

function normalizeMobile(string $mobile): string
{
    $mobile = trim($mobile);
    $mobile = preg_replace('/^\+91/', '', $mobile);
    $mobile = preg_replace('/[\s\-]/', '', $mobile);
    return $mobile;
}

function validatePRN(string $prn): ?string
{
    $prn = trim($prn);
    if (strlen($prn) < 1) {
        return 'PRN is required.';
    }
    if (strlen($prn) > 50) {
        return 'PRN must not exceed 50 characters.';
    }
    return null;
}

function validateFullName(string $name): ?string
{
    $name = trim($name);
    if (strlen($name) < 2) {
        return 'Full name must be at least 2 characters.';
    }
    if (strlen($name) > 100) {
        return 'Full name must not exceed 100 characters.';
    }
    return null;
}

function validateGender(string $gender): ?string
{
    $allowed = ['Male', 'Female', 'Other', 'Prefer not to say'];
    if (!in_array($gender, $allowed, true)) {
        return 'Please select a valid gender.';
    }
    return null;
}

function validateContactName(string $name): ?string
{
    $name = trim($name);
    if (strlen($name) < 2 || strlen($name) > 100) {
        return 'Name must be between 2 and 100 characters.';
    }
    return null;
}

function validateContactSubject(string $subject): ?string
{
    $subject = trim($subject);
    if (strlen($subject) < 3 || strlen($subject) > 200) {
        return 'Subject must be between 3 and 200 characters.';
    }
    return null;
}

function validateContactMessage(string $message): ?string
{
    $message = trim($message);
    if (strlen($message) < 10 || strlen($message) > 5000) {
        return 'Message must be between 10 and 5000 characters.';
    }
    return null;
}

function validateUploadedImage(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Failed to upload payment screenshot. Please try again.';
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return 'Payment screenshot must not exceed 5 MB.';
    }

    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowedMimes, true)) {
        return 'Only JPG, JPEG, and PNG images are allowed.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        return 'Only JPG, JPEG, and PNG images are allowed.';
    }

    return null;
}
