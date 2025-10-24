<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['session_code'])) {
    echo json_encode(['success' => false, 'message' => 'Session code required']);
    exit;
}

$session_code = $input['session_code'];

try {
    // Get session info
    $stmt = $pdo->prepare("
        SELECT qs.*, q.* 
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

    // Check if user is participant
    $stmt = $pdo->prepare("
        SELECT id FROM session_participants 
        WHERE session_id = ? AND user_id = ?
    ");
    $stmt->execute([$session['id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Not a participant']);
        exit;
    }

    // Start the quiz
    $stmt = $pdo->prepare("UPDATE quiz_sessions SET started_at = NOW() WHERE id = ?");
    $stmt->execute([$session['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Quiz started',
        'quiz' => $session
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
