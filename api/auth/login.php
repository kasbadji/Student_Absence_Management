<?php
header('Content-Type: application/json');
session_set_cookie_params(0, '/');
session_start();

require_once __DIR__ . '/../config/db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['identifier']) || empty($input['password'])) {
        echo json_encode(['success' => false, 'message' => 'Identifier and password are required.']);
        exit;
    }

    $identifier = trim($input['identifier']);
    $password = trim($input['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :identifier");
    $stmt->execute(['identifier' => $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $sql = "SELECT u.* , s.matricule FROM users u
                JOIN students s ON u.user_id = s.user_id
                WHERE s.matricule = :identifier";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        $response = [
            'success' => true,
            'message' => 'Login successful',
            'role' => $user['role'],
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name']
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Invalid identifier or password'
        ];
    }
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

