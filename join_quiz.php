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

if (!$input || !isset($input['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
    exit;
}

$quiz_id = (int)$input['quiz_id'];

try {
    // Check if quiz exists and is active
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND is_active = 1");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found or inactive']);
        exit;
    }

    // Generate session code
    $session_code = generateSessionCode();

    // Create quiz session
    $stmt = $pdo->prepare("INSERT INTO quiz_sessions (quiz_id, session_code) VALUES (?, ?)");
    $stmt->execute([$quiz_id, $session_code]);

    $session_id = $pdo->lastInsertId();

    // Add user to session
    $stmt = $pdo->prepare("INSERT INTO session_participants (session_id, user_id) VALUES (?, ?)");
    $stmt->execute([$session_id, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Joined quiz successfully',
        'session_code' => $session_code,
        'quiz' => $quiz
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
