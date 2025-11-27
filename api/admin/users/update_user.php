<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';

// ✅ Only admin can update users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

try {
    // ✅ Get and sanitize input
    $input = json_decode(file_get_contents('php://input'), true);

    $user_id   = $input['user_id'] ?? null;
    $full_name = trim($input['full_name'] ?? '');
    $email     = trim($input['email'] ?? '');
    $password  = trim($input['password'] ?? '');
    $group_id  = $input['group_id'] ?? null;
    $module_id = $input['module_id'] ?? null;

    // ✅ Validate mandatory fields
    if (empty($user_id) || empty($full_name) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID, full name, and email are required.'
        ]);
        exit;
    }

    // ✅ Ensure the user exists and is a teacher
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.email, t.teacher_id
        FROM users u
        JOIN teachers t ON u.user_id = t.user_id
        WHERE u.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        echo json_encode([
            'success' => false,
            'message' => 'Teacher not found.'
        ]);
        exit;
    }

    // ✅ Check if email is already taken (by another user)
    $stmt = $pdo->prepare("
        SELECT 1 FROM users WHERE email = :email AND user_id != :user_id
    ");
    $stmt->execute(['email' => $email, 'user_id' => $user_id]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is already used by another user.'
        ]);
        exit;
    }

    // ✅ Optional: Validate group and module IDs exist (if provided)
    if ($group_id) {
        $stmt = $pdo->prepare("SELECT 1 FROM groups WHERE group_id = :id");
        $stmt->execute(['id' => $group_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID.']);
            exit;
        }
    }

    if ($module_id) {
        $stmt = $pdo->prepare("SELECT 1 FROM modules WHERE module_id = :id");
        $stmt->execute(['id' => $module_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid module ID.']);
            exit;
        }
    }

    // ✅ Begin transaction for safety
    $pdo->beginTransaction();

    // ✅ Update users table
    $sql = "UPDATE users SET full_name = :full_name, email = :email";
    $params = [
        'full_name' => $full_name,
        'email' => $email,
        'user_id' => $user_id
    ];

    // Update password if provided
    if (!empty($password)) {
        $sql .= ", password_hash = :password_hash";
        $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // ✅ Update teacher’s group/module assignment
    $stmt = $pdo->prepare("
        UPDATE teachers
        SET group_id = :group_id, module_id = :module_id
        WHERE user_id = :user_id
    ");
    $stmt->execute([
        'group_id'  => $group_id,
        'module_id' => $module_id,
        'user_id'   => $user_id
    ]);

    // ✅ Commit the transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Teacher updated successfully.',
        'user' => [
            'user_id'   => $user_id,
            'full_name' => $full_name,
            'email'     => $email,
            'group_id'  => $group_id,
            'module_id' => $module_id
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
