<?php
require_once 'config.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_quiz':
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $total_questions = (int)$_POST['total_questions'];
                $time_per_question = (int)$_POST['time_per_question'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, total_questions, time_per_question) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $total_questions, $time_per_question]);
                    $success = 'Quiz added successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to add quiz.';
                }
                break;
                
            case 'add_question':
                $quiz_id = (int)$_POST['quiz_id'];
                $question_text = sanitize($_POST['question_text']);
                $option_a = sanitize($_POST['option_a']);
                $option_b = sanitize($_POST['option_b']);
                $option_c = sanitize($_POST['option_c']);
                $option_d = sanitize($_POST['option_d']);
                $correct_option = strtoupper($_POST['correct_option']);
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option]);
                    $success = 'Question added successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to add question.';
                }
                break;
        }
    }
}

// Get quizzes and stats
try {
    $stmt = $pdo->prepare("SELECT * FROM quizzes ORDER BY created_at DESC");
    $stmt->execute();
    $quizzes = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
    $stmt->execute();
    $total_users = $stmt->fetch()['total_users'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_quizzes FROM quizzes");
    $stmt->execute();
    $total_quizzes = $stmt->fetch()['total_quizzes'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_results FROM results");
    $stmt->execute();
    $total_results = $stmt->fetch()['total_results'];

} catch (PDOException $e) {
    $quizzes = [];
    $total_users = 0;
    $total_quizzes = 0;
    $total_results = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Quiz Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="dashboard.php" class="logo">🎯 Quiz Platform</a>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="admin.php">Admin Panel</a>
            </nav>
            <div class="user-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <button onclick="logout()" class="btn btn-sm btn-danger">Logout</button>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: #667eea; margin-bottom: 30px;">🛠️ Admin Panel</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #4caf50; margin-bottom: 10px;"><?php echo $total_users; ?></h3>
                <p style="color: #666;">Total Users</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 10px;"><?php echo $total_quizzes; ?></h3>
                <p style="color: #666;">Total Quizzes</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="color: #ff9800; margin-bottom: 10px;"><?php echo $total_results; ?></h3>
                <p style="color: #666;">Quiz Attempts</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Add Quiz Form -->
            <div class="card">
                <h3 style="margin-bottom: 20px; color: #333;">Add New Quiz</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_quiz">
                    
                    <div class="form-group">
                        <label for="title">Quiz Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="total_questions">Total Questions</label>
                        <input type="number" id="total_questions" name="total_questions" class="form-control" min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label for="time_per_question">Time per Question (seconds)</label>
                        <input type="number" id="time_per_question" name="time_per_question" class="form-control" min="5" max="60" value="15" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Quiz</button>
                </form>
            </div>

            <!-- Add Question Form -->
            <div class="card">
                <h3 style="margin-bottom: 20px; color: #333;">Add Question</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_question">
                    
                    <div class="form-group">
                        <label for="quiz_id">Select Quiz</label>
                        <select id="quiz_id" name="quiz_id" class="form-control" required>
                            <option value="">Choose a quiz...</option>
                            <?php foreach ($quizzes as $quiz): ?>
                                <option value="<?php echo $quiz['id']; ?>"><?php echo htmlspecialchars($quiz['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="question_text">Question Text</label>
                        <textarea id="question_text" name="question_text" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="option_a">Option A</label>
                        <input type="text" id="option_a" name="option_a" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="option_b">Option B</label>
                        <input type="text" id="option_b" name="option_b" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="option_c">Option C</label>
                        <input type="text" id="option_c" name="option_c" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="option_d">Option D</label>
                        <input type="text" id="option_d" name="option_d" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="correct_option">Correct Option</label>
                        <select id="correct_option" name="correct_option" class="form-control" required>
                            <option value="">Choose correct option...</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>
        </div>

        <!-- Quizzes List -->
        <div class="card" style="margin-top: 30px;">
            <h3 style="margin-bottom: 20px; color: #333;">Manage Quizzes</h3>
            <?php if (empty($quizzes)): ?>
                <p style="color: #666; text-align: center; padding: 20px;">No quizzes found.</p>
            <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div style="padding: 20px; background: #f8f9fa; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin-bottom: 5px; color: #333;"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                <p style="color: #666; margin-bottom: 5px;"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                <small style="color: #999;">
                                    <?php echo $quiz['total_questions']; ?> questions • 
                                    <?php echo $quiz['time_per_question']; ?>s per question • 
                                    <?php echo $quiz['is_active'] ? 'Active' : 'Inactive'; ?>
                                </small>
                            </div>
                            <div>
                                <span style="color: #999; font-size: 0.9rem;">
                                    Created: <?php echo date('M j, Y', strtotime($quiz['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
