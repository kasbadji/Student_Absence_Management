<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// ✅ Only admin can create teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // ✅ Sanitize input
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $group_id = $input['group_id'] ?? null;
    $module_id = $input['module_id'] ?? null;

    // ✅ Validate inputs
    if (empty($full_name) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Full name, email, and password are required.'
        ]);
        exit;
    }

    // ✅ Check if email already exists
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit;
    }

    // ✅ Optional: Validate group_id and module_id exist
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

    // ✅ Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Insert into users table
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash, role, created_at)
        VALUES (:full_name, :email, :password_hash, 'teacher', NOW())
        RETURNING user_id
    ");
    $stmt->execute([
        'full_name' => $full_name,
        'email' => $email,
        'password_hash' => $hashed
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $row['user_id'] ?? null;
    if (!$user_id) {
        throw new Exception('Failed to obtain new user_id after insert');
    }

    // ✅ Generate unique teacher matricule
    $matricule = 'TCH' . str_pad((string) $user_id, 4, '0', STR_PAD_LEFT);

    // ✅ Insert into teachers table (now includes group/module)
    $stmt = $pdo->prepare("
        INSERT INTO teachers (user_id, matricule, group_id, module_id)
        VALUES (:user_id, :matricule, :group_id, :module_id)
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'matricule' => $matricule,
        'group_id' => $group_id,
        'module_id' => $module_id
    ]);

    // ✅ Fetch group and module names for response (optional)
    $group_name = null;
    $title = null;

    if ($group_id) {
        $stmt = $pdo->prepare("SELECT name FROM groups WHERE group_id = :id");
        $stmt->execute(['id' => $group_id]);
        $group_name = $stmt->fetchColumn();
    }

    if ($module_id) {
        $stmt = $pdo->prepare("SELECT title FROM modules WHERE module_id = :id");
        $stmt->execute(['id' => $module_id]);
        $title = $stmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teacher created successfully.',
        'teacher' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email,
            'matricule' => $matricule,
            'group_id' => $group_id,
            'group_name' => $group_name,
            'module_id' => $module_id,
            'title' => $title
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
