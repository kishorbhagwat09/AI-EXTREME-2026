<?php
require_once __DIR__ . '/helpers/functions.php';

$settings = getSiteSettings();
jsonResponse([
    'poster_image' => $settings['poster_image'] ?? 'assets/images/event-poster.jpg',
]);