<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['lat']) && isset($data['lng'])) {
    $_SESSION['user_lat'] = floatval($data['lat']);
    $_SESSION['user_lng'] = floatval($data['lng']);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates']);
}
?>
