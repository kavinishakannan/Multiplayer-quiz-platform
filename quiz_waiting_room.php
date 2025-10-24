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
        SELECT qs.*, q.title, q.description, q.total_questions, q.time_per_question,
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

} catch (PDOException $e) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Room - Quiz Platform</title>
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
                <button onclick="logout()" class="btn btn-sm btn-danger">Logout</button>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="waiting-room">
            <div class="card" style="max-width: 600px; margin: 50px auto;">
                <h2 style="color: #667eea; margin-bottom: 20px;">🎮 Waiting Room</h2>
                
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="color: #333; margin-bottom: 10px;"><?php echo htmlspecialchars($session['title']); ?></h3>
                    <p style="color: #666;"><?php echo htmlspecialchars($session['description']); ?></p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;"><?php echo $session['total_questions']; ?></div>
                        <div style="color: #666;">Questions</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;"><?php echo $session['time_per_question']; ?>s</div>
                        <div style="color: #666;">Per Question</div>
                    </div>
                </div>

                <div class="participants-list">
                    <h4 style="margin-bottom: 15px; color: #333;">Participants</h4>
                    <div id="participants">
                        <div class="spinner"></div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <div class="alert alert-info">
                        <strong>Waiting for quiz to start...</strong><br>
                        The quiz will begin automatically when the host starts it.
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="dashboard.php" class="btn btn-secondary">Leave Room</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.quizPlatform.logout();
            }
        }
    </script>
</body>
</html>
