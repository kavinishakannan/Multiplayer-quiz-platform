<?php
require_once 'config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$session_code = $_GET['session'] ?? '';

if (empty($session_code)) {
    redirect('dashboard.php');
}

// Get session info
try {
    $stmt = $pdo->prepare("
        SELECT qs.*, q.*, q.id as quiz_id,
               CASE WHEN qs.started_at IS NOT NULL THEN 1 ELSE 0 END as quiz_started
        FROM quiz_sessions qs 
        JOIN quizzes q ON qs.quiz_id = q.id 
        WHERE qs.session_code = ?
    ");
    $stmt->execute([$session_code]);
    $session = $stmt->fetch();

    if (!$session) {
        redirect('dashboard.php');
    }

    // Check if user is participant
    $stmt = $pdo->prepare("
        SELECT id FROM session_participants 
        WHERE session_id = ? AND user_id = ?
    ");
    $stmt->execute([$session['id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        redirect('dashboard.php');
    }

    // Check if quiz has started
    if (!$session['quiz_started']) {
        redirect("quiz_waiting_room.php?session=$session_code");
    }

} catch (PDOException $e) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Quiz Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="dashboard.php" class="logo">🎯 Quiz Platform</a>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="leaderboard.php">Leaderboard</a>
            </nav>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="quiz-container">
            <!-- Timer -->
            <div class="timer" id="timer">15</div>

            <!-- Question Container -->
            <div id="questionContainer">
                <div class="question-card">
                    <div style="text-align: center; padding: 40px;">
                        <div class="spinner"></div>
                        <p style="margin-top: 20px; color: #666;">Loading quiz...</p>
                    </div>
                </div>
            </div>

            <!-- Live Leaderboard -->
            <div class="leaderboard">
                <h3 style="margin-bottom: 20px; color: #333;">Live Leaderboard</h3>
                <div id="liveLeaderboard">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Initialize quiz when page loads
        document.addEventListener('DOMContentLoaded', () => {
            const sessionCode = new URLSearchParams(window.location.search).get('session');
            if (sessionCode) {
                window.quizPlatform.sessionCode = sessionCode;
                window.quizPlatform.startQuiz();
            }
        });
    </script>
</body>
</html>
