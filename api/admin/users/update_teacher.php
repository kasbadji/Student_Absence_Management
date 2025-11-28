<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    $user_id = $data['user_id'] ?? null;
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'user_id is required.']);
        exit;
    }

    $group_id = $data['group_id'] ?? null;
    $module_id = $data['module_id'] ?? null;

    $st = $data['session_type'] ?? null;
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
    } elseif ($sl === 'cours' || $sl === 'c' || $sl === 'course') {
        $st = 'COUR';
    } elseif ($sl === 'c/td') {
        $st = 'COUR/TD';
    } elseif ($sl === 'c/tp') {
        $st = 'COUR/TP';
    } else {
        $st = 'TD';
    }
}

    if ($module_id && $st !== null) {
        $stmt = $pdo->prepare('SELECT COALESCE(CAST(has_td AS int),0) AS has_td, COALESCE(CAST(has_tp AS int),0) AS has_tp FROM modules WHERE module_id = :id');
        $stmt->execute(['id' => $module_id]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$m) {
            echo json_encode(['success' => false, 'message' => 'Module not found.']);
            exit;
        }
        $session_lower = strtolower((string) $data['session_type']);
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

    $pdo->beginTransaction();

    $userUpdateParts = [];
    $userParams = [];
    if (isset($data['full_name'])) {
        $userUpdateParts[] = 'full_name = :full_name';
        $userParams['full_name'] = trim($data['full_name']);
    }
    if (isset($data['email'])) {
        $email = trim($data['email']);
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = :email AND user_id != :uid');
        $stmt->execute(['email' => $email, 'uid' => $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Email already exists.');
        }
        $userUpdateParts[] = 'email = :email';
        $userParams['email'] = $email;
    }
    if (isset($data['password']) && trim($data['password']) !== '') {
        $hashed = password_hash(trim($data['password']), PASSWORD_DEFAULT);
        $userUpdateParts[] = 'password_hash = :password_hash';
        $userParams['password_hash'] = $hashed;
    }

    if (count($userUpdateParts) > 0) {
        $userParams['uid'] = $user_id;
        $sql = 'UPDATE users SET ' . implode(', ', $userUpdateParts) . ' WHERE user_id = :uid';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($userParams);
    }

    $stmt = $pdo->prepare('UPDATE teachers SET group_id = :group_id, module_id = :module_id, session_type = :session_type WHERE user_id = :user_id');
    $stmt->execute([
        'group_id' => $group_id === '' ? null : $group_id,
        'module_id' => $module_id === '' ? null : $module_id,
        'session_type' => $st,
        'user_id' => $user_id
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Teacher updated successfully.']);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

