<?php

header('Content-Type: application/json');

$mock_status = isset($_SERVER['HTTP_X_MOCK_STATUS']) ? $_SERVER['HTTP_X_MOCK_STATUS'] : '';

if ($mock_status === 'accepted') {
    $response = json_encode(['status' => 'accepted']);
} elseif ($mock_status === 'failed') {
    $response = json_encode(['status' => 'failed']);
} else {
    $response = json_encode(['error' => 'Invalid or missing X-Mock-Status header']);
}

echo $response;
?>