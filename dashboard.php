<?php
require_once 'config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user stats
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_quizzes FROM results WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_quizzes = $stmt->fetch()['total_quizzes'];

    $stmt = $pdo->prepare("SELECT AVG(score) as avg_score FROM results WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $avg_score = round($stmt->fetch()['avg_score'] ?? 0, 1);

    $stmt = $pdo->prepare("SELECT SUM(score) as total_score FROM results WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_score = $stmt->fetch()['total_score'] ?? 0;

    // Get recent results
    $stmt = $pdo->prepare("
        SELECT r.*, q.title as quiz_title 
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.user_id = ? 
        ORDER BY r.completed_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_results = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_quizzes = 0;
    $avg_score = 0;
    $total_score = 0;
    $recent_results = [];
}

// Get available quizzes
try {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $quizzes = $stmt->fetchAll();
} catch (PDOException $e) {
    $quizzes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="dashboard.php" class="logo">🎯 Quiz Platform</a>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="leaderboard.php">Leaderboard</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>
            </nav>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="profile.php" class="btn btn-sm btn-secondary">Profile</a>
                <button onclick="logout()" class="btn btn-sm btn-danger">Logout</button>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 10px;"><?php echo $total_score; ?></h3>
                <p style="color: #666;">Total Score</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="color: #4caf50; margin-bottom: 10px;"><?php echo $total_quizzes; ?></h3>
                <p style="color: #666;">Quizzes Taken</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="color: #ff9800; margin-bottom: 10px;"><?php echo $avg_score; ?>%</h3>
                <p style="color: #666;">Average Score</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Available Quizzes -->
            <div>
                <h2 style="margin-bottom: 20px; color: #333;">Available Quizzes</h2>
                <div class="quiz-grid">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="quiz-card" data-quiz-id="<?php echo $quiz['id']; ?>">
                            <div class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
                            <div class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></div>
                            <div class="quiz-meta">
                                <div class="quiz-questions"><?php echo $quiz['total_questions']; ?> Questions</div>
                                <div class="quiz-time"><?php echo $quiz['time_per_question']; ?>s per question</div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%;">Join Quiz</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Activity & Leaderboard -->
            <div>
                <!-- Recent Activity -->
                <div class="card">
                    <h3 style="margin-bottom: 20px; color: #333;">Recent Activity</h3>
                    <?php if (empty($recent_results)): ?>
                        <p style="color: #666; text-align: center; padding: 20px;">No quizzes taken yet!</p>
                    <?php else: ?>
                        <?php foreach ($recent_results as $result): ?>
                            <div style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px;">
                                <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($result['quiz_title']); ?></div>
                                <div style="color: #666; font-size: 14px;">
                                    Score: <?php echo $result['score']; ?>/<?php echo $result['total']; ?> 
                                    (<?php echo round(($result['score'] / $result['total']) * 100, 1); ?>%)
                                </div>
                                <div style="color: #999; font-size: 12px;">
                                    <?php echo date('M j, Y g:i A', strtotime($result['completed_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Leaderboard -->
                <div class="leaderboard">
                    <h3 style="margin-bottom: 20px; color: #333;">Top Players</h3>
                    <div id="leaderboard">
                        <div class="spinner"></div>
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
