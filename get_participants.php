<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$session_code = $_GET['session'] ?? '';

if (empty($session_code)) {
    echo json_encode(['success' => false, 'message' => 'Session code required']);
    exit;
}

try {
    // Get session info
    $stmt = $pdo->prepare("
        SELECT qs.*, q.title, q.description, q.total_questions, q.time_per_question,
               CASE WHEN qs.started_at IS NOT NULL THEN 1 ELSE 0 END as quiz_started
        FROM quiz_sessions qs 
        JOIN quizzes q ON qs.quiz_id = q.id 
        WHERE qs.session_code = ?
    ");
    $stmt->execute([$session_code]);
    $session = $stmt->fetch();

    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
        exit;
    }

    // Get participants
    $stmt = $pdo->prepare("
        SELECT u.username 
        FROM session_participants sp 
        JOIN users u ON sp.user_id = u.id 
        WHERE sp.session_id = ?
        ORDER BY sp.joined_at ASC
    ");
    $stmt->execute([$session['id']]);
    $participants = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $participants,
        'quiz_started' => (bool)$session['quiz_started'],
        'session' => $session
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
