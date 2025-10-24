<?php
require_once 'config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get global leaderboard
try {
    $stmt = $pdo->prepare("
        SELECT u.username, u.total_score, COUNT(r.id) as quizzes_taken,
               AVG(ROUND((r.score / r.total) * 100, 1)) as avg_percentage
        FROM users u 
        LEFT JOIN results r ON u.id = r.user_id 
        WHERE u.role = 'user'
        GROUP BY u.id 
        ORDER BY u.total_score DESC, avg_percentage DESC
        LIMIT 50
    ");
    $stmt->execute();
    $leaderboard = $stmt->fetchAll();

    // Get user's rank
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as rank
        FROM users u 
        WHERE u.total_score > (SELECT total_score FROM users WHERE id = ?)
        AND u.role = 'user'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_rank = $stmt->fetch()['rank'];

    // Get user's stats
    $stmt = $pdo->prepare("SELECT total_score FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_stats = $stmt->fetch();

} catch (PDOException $e) {
    $leaderboard = [];
    $user_rank = 0;
    $user_stats = ['total_score' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Quiz Platform</title>
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
        <div class="card">
            <h1 style="color: #667eea; margin-bottom: 30px; text-align: center;">🏆 Global Leaderboard</h1>
            
            <!-- User's Current Rank -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; text-align: center;">
                <h3 style="margin-bottom: 10px;">Your Current Rank</h3>
                <div style="font-size: 2rem; font-weight: bold;">#<?php echo $user_rank; ?></div>
                <div style="font-size: 1.2rem; margin-top: 10px;"><?php echo $user_stats['total_score']; ?> Total Points</div>
            </div>

            <!-- Leaderboard -->
            <div class="leaderboard">
                <?php if (empty($leaderboard)): ?>
                    <p style="color: #666; text-align: center; padding: 40px;">No players yet!</p>
                <?php else: ?>
                    <?php foreach ($leaderboard as $index => $player): ?>
                        <div class="leaderboard-item rank-<?php echo min($index + 1, 3); ?>">
                            <div class="rank">#<?php echo $index + 1; ?></div>
                            <div class="player-name">
                                <strong><?php echo htmlspecialchars($player['username']); ?></strong>
                                <?php if ($player['username'] === $_SESSION['username']): ?>
                                    <span style="color: #667eea; font-size: 0.9rem;">(You)</span>
                                <?php endif; ?>
                            </div>
                            <div class="player-score">
                                <div style="font-weight: bold; color: #667eea;"><?php echo $player['total_score']; ?> pts</div>
                                <div style="font-size: 0.8rem; color: #666;">
                                    <?php echo $player['quizzes_taken']; ?> quizzes
                                    <?php if ($player['avg_percentage']): ?>
                                        • <?php echo round($player['avg_percentage'], 1); ?>% avg
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
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
