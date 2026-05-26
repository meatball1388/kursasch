<?php
session_start();
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// echo json_encode($data);
// exit;

if ($data && isset($data['email'])) {
    $_SESSION['user'] = [
        'id' => $data['id'] ?? null,
        'email' => $data['email'],
        'name' => $data['name'] ?? '',
        'surname' => $data['surname'] ?? '',
        'role' => $data['role'] ?? 'user',
        'logged_in' => true,
        'login_time' => time()
    ];
    // echo json_encode($_SESSION);
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
} else {
    http_response_code(400);
    echo json_encode(['success' => false]);
}
