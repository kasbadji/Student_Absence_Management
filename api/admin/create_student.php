<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

//! Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['full_name']) || empty($input['matricule']) || empty($input['password'])) {
        echo json_encode(['success' => false, 'message' => 'Full name, matricule, and password are required']);
        exit;
    }

    $full_name = trim($input['full_name']);
    $matricule = trim($input['matricule']);
    $password = trim($input['password']);

    //! Check if matricule exists
    $stmt = $pdo->prepare("SELECT 1 FROM students WHERE matricule = :matricule");
    $stmt->execute(['matricule' => $matricule]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Matricule already exists']);
        exit;
    }

    //! Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    //! Insert into users
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at)
                           VALUES (:full_name, NULL, :password_hash, 'student', NOW())
                           RETURNING user_id");

    $stmt->execute([
        'full_name' => $full_name,
        'password_hash' => $hashed
    ]);

    $user_id = $stmt->fetchColumn();

    //! Insert into students table
    $stmt = $pdo->prepare("INSERT INTO students (user_id, matricule) VALUES (:user_id, :matricule)");
    $stmt->execute([
        'user_id' => $user_id,
        'matricule' => $matricule
    ]);

    echo json_encode(['success' => true, 'message' => 'Student account created successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
