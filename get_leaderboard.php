<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    // Get top 10 users by total score
    $stmt = $pdo->prepare("
        SELECT u.username, u.total_score 
        FROM users u 
        ORDER BY u.total_score DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $leaderboard = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $leaderboard
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
