<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

//! Only admin can create teachers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['full_name']) || empty($input['email']) || empty($input['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Full name, email, and password are required.'
        ]);
        exit;
    }

    $full_name = trim($input['full_name']);
    $email     = trim($input['email']);
    $password  = trim($input['password']);

    //! Check for existing email
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit;
    }

    //! Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    //! Insert into users table
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash, role, created_at)
        VALUES (:full_name, :email, :password_hash, 'teacher', NOW())
        RETURNING user_id
    ");
    $stmt->execute([
        'full_name'     => $full_name,
        'email'         => $email,
        'password_hash' => $hashed
    ]);
    $user_id = $stmt->fetchColumn();

    //! Generate a unique teacher matricule
    $matricule = 'TCH' . str_pad((string)$user_id, 4, '0', STR_PAD_LEFT);

    //! Insert into teachers table with user_id and matricule
    $stmt = $pdo->prepare("
        INSERT INTO teachers (user_id, matricule)
        VALUES (:user_id, :matricule)
    ");
    $stmt->execute([
        'user_id'   => $user_id,
        'matricule' => $matricule
    ]);

    //! Success response
    echo json_encode([
        'success' => true,
        'message' => 'Teacher created successfully.',
        'teacher' => [
            'user_id'   => $user_id,
            'full_name' => $full_name,
            'email'     => $email,
            'matricule' => $matricule
        ]
    ]);
}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
