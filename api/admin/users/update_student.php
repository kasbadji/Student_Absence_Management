<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $full_name = isset($input['full_name']) ? trim($input['full_name']) : null;
    $email = null;
    if (array_key_exists('email', $input)) {
        if (is_null($input['email'])) {
            $email = null;
        } else {
            $tmp = trim($input['email']);
            $email = $tmp === '' ? null : $tmp;
        }
    }
    $password = trim($input['password'] ?? '');
    $group_id = array_key_exists('group_id', $input) ? $input['group_id'] : null;

    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'user_id is required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT u.user_id, u.full_name AS current_full_name, u.email AS current_email, s.student_id FROM users u JOIN students s ON u.user_id = s.user_id WHERE u.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit;
    }

    if ($full_name === null)
        $full_name = $student['current_full_name'] ?? '';
    if ($email === null)
        $email = $student['current_email'] ?? '';

    if (empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'User ID and full name are required.']);
        exit;
    }

    if ($email !== null && $email !== ($student['current_email'] ?? null)) {
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = :email AND user_id != :user_id");
        $stmt->execute(['email' => $email, 'user_id' => $user_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already used by another user.']);
            exit;
        }
    }

    if ($group_id !== null && $group_id !== '') {
        $stmt = $pdo->prepare("SELECT 1 FROM groups WHERE group_id = :id");
        $stmt->execute(['id' => $group_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID.']);
            exit;
        }
    }

    $pdo->beginTransaction();

    $userUpdateParts = ['full_name = :full_name'];
    $userParams = ['full_name' => $full_name, 'user_id' => $user_id];
    if ($email !== null) {
        $userUpdateParts[] = 'email = :email';
        $userParams['email'] = $email;
    }
    if (!empty($password)) {
        $userUpdateParts[] = 'password_hash = :password_hash';
        $userParams['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    if (count($userUpdateParts) > 0) {
        $sql = 'UPDATE users SET ' . implode(', ', $userUpdateParts) . ' WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($userParams);
    }

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

