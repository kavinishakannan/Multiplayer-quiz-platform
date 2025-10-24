<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$quiz_id = (int)($_GET['quiz_id'] ?? 0);
$question_index = (int)($_GET['question'] ?? 0);

if (!$quiz_id || $question_index < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Get quiz info
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit;
    }

    if ($question_index >= $quiz['total_questions']) {
        echo json_encode(['success' => false, 'message' => 'Question not found']);
        exit;
    }

    // Get question
    $stmt = $pdo->prepare("
        SELECT * FROM questions 
        WHERE quiz_id = ? 
        ORDER BY id 
        LIMIT 1 OFFSET ?
    ");
    $stmt->execute([$quiz_id, $question_index]);
    $question = $stmt->fetch();

    if (!$question) {
        echo json_encode(['success' => false, 'message' => 'Question not found']);
        exit;
    }

    // Remove correct answer from response for security
    unset($question['correct_option']);

    echo json_encode([
        'success' => true,
        'question' => $question
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
