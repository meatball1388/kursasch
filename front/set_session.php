<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['id'])) {
    $_SESSION['user'] = [
        'logged_in' => true,
        'id' => $data['id'],
        'email' => $data['email'] ?? '',
        'name' => $data['name'] ?? '',
        'surname' => $data['surname'] ?? '',
        'role' => $data['role'] ?? 'user',
        'phone' => $data['phone'] ?? '',
        'passport' => $data['passport'] ?? ''
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
