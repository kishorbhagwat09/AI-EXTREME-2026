<?php
/**
 * AI EXTREME 2026 - Configuration Example
 * Copy this file to config.php and update with your credentials.
 * NEVER commit config.php to version control.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_extreme_2026');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

define('SITE_URL', 'http://localhost/AI-EXTREME-2026');
define('UPLOAD_PATH', __DIR__ . '/uploads/payments/');
define('UPLOAD_URL', 'uploads/payments/');
define('SITE_MEDIA_PATH', __DIR__ . '/uploads/site-media/');
define('SITE_MEDIA_URL', 'uploads/site-media/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);
