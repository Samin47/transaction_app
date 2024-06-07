<?php

require_once 'configurations/db_config.php';
require_once 'configurations/config.php';
require_once 'configurations/common.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$transaction_id = $data['transaction_id'] ?? null;
$status = $data['status'] ?? null;

if (!$transaction_id || !$status || !in_array($status, ['accepted', 'failed'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input: Missing or invalid transaction_id or status']);
    exit;
}

$mysqli = db_connect();

// Check if transaction ID exists
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
$stmt->bind_param('s', $transaction_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count == 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input: Transaction ID does not exist']);
    exit;
}

// Update records
$stmt = $mysqli->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
$stmt->bind_param('ss', $status, $transaction_id);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Transaction updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update transaction']);
}

$stmt->close();
$mysqli->close();
?>
