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

// Get session and result info
try {
    $stmt = $pdo->prepare("
        SELECT qs.*, q.title, q.description, q.total_questions,
               r.score, r.total, r.completed_at
        FROM quiz_sessions qs 
        JOIN quizzes q ON qs.quiz_id = q.id 
        LEFT JOIN results r ON r.quiz_id = q.id AND r.user_id = ?
        WHERE qs.session_code = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $session_code]);
    $result = $stmt->fetch();

    if (!$result) {
        redirect('dashboard.php');
    }

    // Get leaderboard for this quiz
    $stmt = $pdo->prepare("
        SELECT u.username, r.score, r.total,
               ROUND((r.score / r.total) * 100, 1) as percentage
        FROM results r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.quiz_id = ? 
        ORDER BY r.score DESC, r.completed_at ASC
        LIMIT 10
    ");
    $stmt->execute([$result['quiz_id']]);
    $leaderboard = $stmt->fetchAll();

    // Calculate user's rank
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as rank
        FROM results r 
        WHERE r.quiz_id = ? AND r.score > ?
    ");
    $stmt->execute([$result['quiz_id'], $result['score']]);
    $user_rank = $stmt->fetch()['rank'];

} catch (PDOException $e) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Quiz Platform</title>
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
        <div class="results-container">
            <div class="card" style="max-width: 800px; margin: 50px auto;">
                <h2 style="color: #667eea; margin-bottom: 20px; text-align: center;">🎉 Quiz Completed!</h2>
                
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="color: #333; margin-bottom: 10px;"><?php echo htmlspecialchars($result['title']); ?></h3>
                    <p style="color: #666;"><?php echo htmlspecialchars($result['description']); ?></p>
                </div>

                <!-- Score Circle -->
                <div class="score-circle">
                    <div class="score-text">Your Score</div>
                    <div class="score-number"><?php echo $result['score']; ?>/<?php echo $result['total']; ?></div>
                    <div style="font-size: 1rem; margin-top: 10px;">
                        <?php echo round(($result['score'] / $result['total']) * 100, 1); ?>%
                    </div>
                </div>

                <!-- Performance Stats -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin: 30px 0;">
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 2rem; font-weight: bold; color: #4caf50;"><?php echo $result['score']; ?></div>
                        <div style="color: #666;">Correct Answers</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 2rem; font-weight: bold; color: #f44336;"><?php echo $result['total'] - $result['score']; ?></div>
                        <div style="color: #666;">Incorrect Answers</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 2rem; font-weight: bold; color: #ff9800;">#<?php echo $user_rank; ?></div>
                        <div style="color: #666;">Your Rank</div>
                    </div>
                </div>

                <!-- Quiz Leaderboard -->
                <div class="leaderboard">
                    <h3 style="margin-bottom: 20px; color: #333; text-align: center;">Quiz Leaderboard</h3>
                    <?php if (empty($leaderboard)): ?>
                        <p style="color: #666; text-align: center; padding: 20px;">No results yet!</p>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $index => $player): ?>
                            <div class="leaderboard-item rank-<?php echo min($index + 1, 3); ?>">
                                <div class="rank">#<?php echo $index + 1; ?></div>
                                <div class="player-name"><?php echo htmlspecialchars($player['username']); ?></div>
                                <div class="player-score"><?php echo $player['score']; ?>/<?php echo $player['total']; ?> (<?php echo $player['percentage']; ?>%)</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 30px;">
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    <a href="leaderboard.php" class="btn btn-secondary">Global Leaderboard</a>
                    <a href="quiz_waiting_room.php?session=<?php echo $session_code; ?>" class="btn btn-success">Retake Quiz</a>
                </div>

                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e5e9;">
                    <small style="color: #666;">
                        Completed on <?php echo date('M j, Y g:i A', strtotime($result['completed_at'])); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
