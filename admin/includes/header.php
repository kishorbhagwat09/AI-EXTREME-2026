<?php
require_once dirname(__DIR__, 2) . '/php/helpers/functions.php';
require_once dirname(__DIR__, 2) . '/php/helpers/security.php';
requireAdmin();
initSession();
$adminName = $_SESSION['admin_username'] ?? 'Admin';
$stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeOutput($pageTitle ?? 'Admin') ?> | AI Extreme 2026</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<button class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
<div class="admin-layout">
