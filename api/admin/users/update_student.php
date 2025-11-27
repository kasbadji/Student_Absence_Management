<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';

// Only admin can update students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $full_name = trim($input['full_name'] ?? '');
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = trim($input['password'] ?? '');
    $group_id = $input['group_id'] ?? null;

    if (empty($user_id) || empty($full_name) || $email === '') {
        echo json_encode(['success' => false, 'message' => 'User ID, full name, and email are required.']);
        exit;
    }

    // Ensure the user exists and is a student
    $stmt = $pdo->prepare("SELECT u.user_id, s.student_id FROM users u JOIN students s ON u.user_id = s.user_id WHERE u.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit;
    }

    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = :email AND user_id != :user_id");
    $stmt->execute(['email' => $email, 'user_id' => $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email is already used by another user.']);
        exit;
    }

    // Validate group if provided
    if ($group_id) {
        $stmt = $pdo->prepare("SELECT 1 FROM groups WHERE group_id = :id");
        $stmt->execute(['id' => $group_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID.']);
            exit;
        }
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Update users table
    $sql = "UPDATE users SET full_name = :full_name, email = :email";
    $params = ['full_name' => $full_name, 'email' => $email, 'user_id' => $user_id];
    if (!empty($password)) {
        $sql .= ", password_hash = :password_hash";
        $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $sql .= " WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update students table (group assignment)
    $stmt = $pdo->prepare("UPDATE students SET group_id = :group_id WHERE user_id = :user_id");
    $stmt->execute(['group_id' => $group_id, 'user_id' => $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Student updated successfully.',
        'user' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email,
            'group_id' => $group_id
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

