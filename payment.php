<?php

require_once 'configurations/db_config.php';
require_once 'configurations/config.php';
require_once 'configurations/common.php';


header('Content-Type: application/json');
header('Cache-Control: no-store');

$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'] ?? null;
$user_id = $data['user_id'] ?? null;

if (!$amount || !$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$mock_status = isset($_SERVER['HTTP_X_MOCK_STATUS']) ? $_SERVER['HTTP_X_MOCK_STATUS'] : 'accepted';

// Call the mock response API
$mock_response_url = API_BASE_URL . '/mock_response.php';
$response = send_request($mock_response_url, 'GET', ['X-Mock-Status: ' . $mock_status]);

if (!$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve mock response']);
    exit;
}

$response_data = json_decode($response, true);

if (!isset($response_data['status'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid mock response format']);
    exit;
}

$status = $response_data['status'];
$transaction_id = generate_transaction_id();


//connect to db and insert record
$mysqli = db_connect();

$stmt = $mysqli->prepare("INSERT INTO transactions (transaction_id, user_id, amount, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param('sids', $transaction_id, $user_id, $amount, $status);

if ($stmt->execute()) {
    echo json_encode(['transaction_id' => $transaction_id, 'status' => $status]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to store transaction']);
}

$stmt->close();
$mysqli->close();


function generate_transaction_id($prefix = 'txn_', $length = 16) {
    $bytes = random_bytes($length);
    return $prefix . bin2hex($bytes);
}
?>