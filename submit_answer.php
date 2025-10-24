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

if (!$input || !isset($input['session_code']) || !isset($input['question_index']) || !isset($input['answer'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$session_code = $input['session_code'];
$question_index = (int)$input['question_index'];
$answer = $input['answer'];

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

    // Get the correct answer for this question
    $stmt = $pdo->prepare("
        SELECT correct_option 
        FROM questions 
        WHERE quiz_id = ? 
        ORDER BY id 
        LIMIT 1 OFFSET ?
    ");
    $stmt->execute([$session['quiz_id'], $question_index]);
    $question = $stmt->fetch();

    if (!$question) {
        echo json_encode(['success' => false, 'message' => 'Question not found']);
        exit;
    }

    $correct = ($answer === $question['correct_option']);

    echo json_encode([
        'success' => true,
        'correct' => $correct,
        'correct_answer' => $question['correct_option']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
