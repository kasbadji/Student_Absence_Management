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

    $full_name = trim($input['full_name']);
    $password = trim($input['password']);
    $group_id = $input['group_id'] ?? null;

    if (empty($full_name) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Full name and password are required']);
        exit;
    }

    //! Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    //! Insert into users table
    $stmt = $pdo->prepare(
        "INSERT INTO users (full_name, email, password_hash, role, created_at)
        VALUES (:full_name, NULL, :password_hash, 'student', NOW())
        RETURNING user_id"
    );
    $stmt->execute([
        'full_name' => $full_name,
        'password_hash' => $hashed
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $row['user_id'] ?? null;

    if (!$user_id) {
        throw new Exception('Failed to obtain new user_id after insert');
    }

    //! Generate unique matricule like STD0007
    $matricule = 'STD' . str_pad((string) $user_id, 4, '0', STR_PAD_LEFT);

    //! Insert into students table
    $stmt = $pdo->prepare("
        INSERT INTO students (user_id, matricule, group_id)
        VALUES (:user_id, :matricule, :group_id)
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'matricule' => $matricule,
        'group_id' => $group_id
    ]);

    //! Return success
    echo json_encode([
        'success' => true,
        'message' => 'Student account created successfully.',
        'student' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'matricule' => $matricule,
            'group_id' => $group_id
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

