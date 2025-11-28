<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $group_id = $input['group_id'] ?? null;
    $module_id = $input['module_id'] ?? null;
    $session_type = trim($input['session_type'] ?? '');

    if (empty($user_id) || empty($full_name) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID, full name, and email are required.'
        ]);
        exit;
    }

    //! Ensure the user exists and is a teacher
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

    //! Check if email is already taken
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

    //! Optional: Validate group and module IDs exist
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

    //! Validate requested session_type against module capabilities
    if ($module_id) {
        $stmt = $pdo->prepare("SELECT COALESCE(CAST(has_td AS int),0) AS has_td, COALESCE(CAST(has_tp AS int),0) AS has_tp FROM modules WHERE module_id = :id");
        $stmt->execute(['id' => $module_id]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$m) {
            echo json_encode(['success' => false, 'message' => 'Module not found.']);
            exit;
        }
        $session_lower = strtolower($session_type);
        $needs_td = ($session_lower === 'all') || (strpos($session_lower, 'td') !== false);
        $needs_tp = ($session_lower === 'all') || (strpos($session_lower, 'tp') !== false);
        if ($needs_td && empty($m['has_td'])) {
            echo json_encode(['success' => false, 'message' => 'Selected module does not have TD sessions.']);
            exit;
        }
        if ($needs_tp && empty($m['has_tp'])) {
            echo json_encode(['success' => false, 'message' => 'Selected module does not have TP sessions.']);
            exit;
        }
    }

    //! Begin transaction for safety
    $pdo->beginTransaction();

    //! Update users table
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

    //! Update teacher’s group/module/session assignment
    $stmt = $pdo->prepare("
        UPDATE teachers
        SET group_id = :group_id, module_id = :module_id, session_type = :session_type
        WHERE user_id = :user_id
    ");
    $st = $session_type !== '' ? $session_type : null;

if ($st !== null) {
    $sl = strtolower(trim($st));

    if ($sl === 'all') {
        $st = 'ALL';
    } elseif ($sl === 'td/tp' || $sl === 'tdtp') {
        $st = 'TD/TP';
    } elseif ($sl === 'td') {
        $st = 'TD';
    } elseif ($sl === 'tp') {
        $st = 'TP';
    } elseif (
        $sl === 'cours' || $sl === 'c' || $sl === 'course' ||
        $sl === 'c/td' || $sl === 'c/tp'
    ) {
        $st = 'COUR';
    } else {
        $st = 'TD';
    }
}

$stmt->execute([
    'group_id' => $group_id,
    'module_id' => $module_id,
    'session_type' => $st,
    'user_id' => $user_id
]);

    //! Commit the transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Teacher updated successfully.',
        'user' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email,
            'group_id' => $group_id,
            'module_id' => $module_id,
            'session_type' => $session_type
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
