<?php
/**
 * API endpoint to calculate current registration fee
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../helpers/functions.php';

try {
    $settings = getSiteSettings();
    $fee = calculateRegistrationFee($settings);
    $earlyBird = isEarlyBird($settings);

    echo json_encode([
        'success'    => true,
        'fee'        => $fee,
        'fee_display'=> formatCurrency($fee),
        'early_bird' => $earlyBird,
        'early_bird_fee' => (float) $settings['early_bird_fee'],
        'regular_fee'    => (float) $settings['regular_fee'],
        'early_bird_deadline' => $settings['early_bird_deadline'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to calculate fee.']);
}
