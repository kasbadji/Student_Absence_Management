<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

//! Only admin can create students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['full_name']) || empty($input['password'])) {
        echo json_encode(['success' => false, 'message' => 'Full name and password are required']);
        exit;
    }

    $full_name = trim($input['full_name']);
    $password = trim($input['password']);

    //! Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    //! Insert into users table
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash, role, created_at)
        VALUES (:full_name, NULL, :password_hash, 'student', NOW())
        RETURNING user_id
    ");
    $stmt->execute([
        'full_name' => $full_name,
        'password_hash' => $hashed
    ]);
    $user_id = $stmt->fetchColumn();

    //! Generate unique matricule like STD0007
    $matricule = 'STD' . str_pad((string) $user_id, 4, '0', STR_PAD_LEFT);

    //! Insert into students table
    $stmt = $pdo->prepare("
        INSERT INTO students (user_id, matricule)
        VALUES (:user_id, :matricule)
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'matricule' => $matricule
    ]);

    //! Return success
    echo json_encode([
        'success' => true,
        'message' => 'Student account created successfully.',
        'student' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'matricule' => $matricule
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

