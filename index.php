<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Quiz Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #667eea; margin-bottom: 10px;">🎯 Quiz Platform</h1>
                <p style="color: #666;">Test your knowledge in real-time!</p>
            </div>

            <div id="alertContainer"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <a href="login.php" class="btn btn-primary" style="text-align: center;">
                    🔑 Login
                </a>
                <a href="register.php" class="btn btn-secondary" style="text-align: center;">
                    📝 Register
                </a>
            </div>

            <div style="text-align: center;">
                <h3 style="margin-bottom: 20px; color: #333;">Features</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left;">
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong>⚡ Real-Time</strong><br>
                        <small>Live multiplayer quizzes</small>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong>🏆 Leaderboard</strong><br>
                        <small>Compete with others</small>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong>⏱️ Timed Questions</strong><br>
                        <small>Quick thinking required</small>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong>📊 Analytics</strong><br>
                        <small>Track your progress</small>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e5e9;">
                <small style="color: #666;">
                    Built with ❤️ using PHP, MySQL, HTML, CSS & JavaScript
                </small>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
