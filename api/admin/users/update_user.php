<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

try {
  $data = json_decode(file_get_contents('php://input'), true);
  $user_id   = $data['user_id'] ?? null;
  $full_name = trim($data['full_name'] ?? '');
  $email     = trim($data['email'] ?? '');
  $password  = trim($data['password'] ?? '');
  $group_id  = $data['group_id'] ?? null;     // 👈 New

  if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id.']);
    exit;
  }

  // ---------- Update users table ----------
  $fields = ['full_name = :full_name'];
  $params = ['full_name' => $full_name, 'user_id' => $user_id];

  if (!empty($email)) {
    $fields[] = 'email = :email';
    $params['email'] = $email;
  }
  if (!empty($password)) {
    $fields[] = 'password_hash = :password_hash';
    $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
  }

  $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = :user_id';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  // ---------- Update group (students table) ----------
  if ($group_id !== null && $group_id !== '') {
    $stmt = $pdo->prepare('UPDATE students SET group_id = :gid WHERE user_id = :uid');
    $stmt->execute(['gid' => $group_id, 'uid' => $user_id]);
  }

  echo json_encode(['success' => true, 'message' => 'User updated successfully']);
}
catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
