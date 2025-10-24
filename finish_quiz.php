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

if (!$input || !isset($input['session_code']) || !isset($input['score']) || !isset($input['total'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$session_code = $input['session_code'];
$score = (int)$input['score'];
$total = (int)$input['total'];

try {
    // Get session info
    $stmt = $pdo->prepare("
        SELECT qs.*, q.id as quiz_id 
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

    // Save result
    $stmt = $pdo->prepare("
        INSERT INTO results (user_id, quiz_id, score, total) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $session['quiz_id'], $score, $total]);

    // Update user's total score
    $stmt = $pdo->prepare("UPDATE users SET total_score = total_score + ? WHERE id = ?");
    $stmt->execute([$score, $_SESSION['user_id']]);

    // End the session
    $stmt = $pdo->prepare("UPDATE quiz_sessions SET ended_at = NOW() WHERE id = ?");
    $stmt->execute([$session['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Quiz completed successfully',
        'result' => [
            'score' => $score,
            'total' => $total,
            'percentage' => round(($score / $total) * 100, 1)
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
