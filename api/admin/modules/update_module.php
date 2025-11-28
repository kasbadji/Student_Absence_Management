<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

//! Only admin can create students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['module_id'] ?? null;
$title = trim($data['title'] ?? '');
$has_td = isset($data['has_td']) ? (int) $data['has_td'] : null;
$has_tp = isset($data['has_tp']) ? (int) $data['has_tp'] : null;

if (!$id || !$title) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

try {
    $fields = ['title' => ':title'];
    $params = [':title' => $title, ':id' => $id];
    if ($has_td !== null) {
        $fields['has_td'] = ':has_td';
        $params[':has_td'] = $has_td;
    }
    if ($has_tp !== null) {
        $fields['has_tp'] = ':has_tp';
        $params[':has_tp'] = $has_tp;
    }

    $setSql = implode(', ', array_map(function ($k, $v) {
        return "$k = $v"; }, array_keys($fields), $fields));
    $sql = "UPDATE modules SET $setSql WHERE module_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Module updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

