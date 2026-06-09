<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['email']) && isset($data['password'])) {
    $backend_url = getenv('BACKEND_URL');
    if (!$backend_url) {
        $backend_url = 'http://127.0.0.1:8000';
        if (gethostbyname('backend') !== 'backend') {
            $backend_url = 'http://backend:8000';
        }
    }
    
    $ch = curl_init($backend_url . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $data['email'], 
        'password' => $data['password']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $httpcode == 200) {
        $result = json_decode($response, true);
        if (isset($result['success']) && $result['success'] === 'true') {
            $_SESSION['user'] = [
                'logged_in' => true,
                'id' => $result['id'],
                'email' => $result['email'] ?? '',
                'name' => $result['name'] ?? '',
                'surname' => $result['surname'] ?? '',
                'role' => $result['role'] ?? 'user',
                'phone' => $result['phone'] ?? ''
            ];
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Неверный логин или пароль']);
            exit;
        }
    } else {
         echo json_encode(['success' => false, 'message' => 'Ошибка связи с сервером']);
         exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
