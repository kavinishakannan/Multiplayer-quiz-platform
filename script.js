// Real-Time Quiz Platform JavaScript
class QuizPlatform {
    constructor() {
        this.currentQuiz = null;
        this.currentQuestion = 0;
        this.score = 0;
        this.timer = null;
        this.timeLeft = 0;
        this.answers = [];
        this.sessionCode = null;
        this.pollInterval = null;
    }

    // Initialize the platform
    init() {
        this.setupEventListeners();
        this.startPolling();
    }

    // Setup event listeners
    setupEventListeners() {
        // Quiz card clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.quiz-card')) {
                const quizId = e.target.closest('.quiz-card').dataset.quizId;
                this.joinQuiz(quizId);
            }
        });

        // Option selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.option')) {
                this.selectOption(e.target.closest('.option'));
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'loginForm') {
                e.preventDefault();
                this.handleLogin(e.target);
            } else if (e.target.id === 'registerForm') {
                e.preventDefault();
                this.handleRegister(e.target);
            }
        });
    }

    // Start polling for real-time updates
    startPolling() {
        this.pollInterval = setInterval(() => {
            this.updateLeaderboard();
            this.updateWaitingRoom();
        }, 2000);
    }

    // Stop polling
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
    }

    // Handle user login
    async handleLogin(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Login successful!', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                this.showAlert(result.message || 'Login failed!', 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Handle user registration
    async handleRegister(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Registration successful! Please login.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1000);
            } else {
                this.showAlert(result.message || 'Registration failed!', 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Join a quiz
    async joinQuiz(quizId) {
        try {
            const response = await fetch('api/join_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ quiz_id: quizId })
            });

            const result = await response.json();

            if (result.success) {
                this.sessionCode = result.session_code;
                window.location.href = `quiz_waiting_room.php?session=${this.sessionCode}`;
            } else {
                this.showAlert(result.message || 'Failed to join quiz!', 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Update leaderboard
    async updateLeaderboard() {
        const leaderboardElement = document.getElementById('leaderboard');
        if (!leaderboardElement) return;

        try {
            const response = await fetch('api/get_leaderboard.php');
            const result = await response.json();

            if (result.success) {
                this.renderLeaderboard(result.data);
            }
        } catch (error) {
            console.error('Error updating leaderboard:', error);
        }
    }

    // Render leaderboard
    renderLeaderboard(data) {
        const leaderboardElement = document.getElementById('leaderboard');
        if (!leaderboardElement) return;

        leaderboardElement.innerHTML = data.map((player, index) => `
            <div class="leaderboard-item rank-${index + 1}">
                <div class="rank">#${index + 1}</div>
                <div class="player-name">${player.username}</div>
                <div class="player-score">${player.total_score} pts</div>
            </div>
        `).join('');
    }

    // Update waiting room
    async updateWaitingRoom() {
        const participantsElement = document.getElementById('participants');
        if (!participantsElement) return;

        const sessionCode = new URLSearchParams(window.location.search).get('session');
        if (!sessionCode) return;

        try {
            const response = await fetch(`api/get_participants.php?session=${sessionCode}`);
            const result = await response.json();

            if (result.success) {
                this.renderParticipants(result.data);
                
                // Check if quiz has started
                if (result.quiz_started) {
                    window.location.href = `quiz.php?session=${sessionCode}`;
                }
            }
        } catch (error) {
            console.error('Error updating waiting room:', error);
        }
    }

    // Render participants
    renderParticipants(participants) {
        const participantsElement = document.getElementById('participants');
        if (!participantsElement) return;

        participantsElement.innerHTML = participants.map(participant => `
            <div class="participant">
                <div class="participant-avatar">${participant.username.charAt(0).toUpperCase()}</div>
                <div class="participant-name">${participant.username}</div>
            </div>
        `).join('');
    }

    // Start quiz
    async startQuiz() {
        const sessionCode = new URLSearchParams(window.location.search).get('session');
        if (!sessionCode) return;

        try {
            const response = await fetch('api/start_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ session_code: sessionCode })
            });

            const result = await response.json();

            if (result.success) {
                this.currentQuiz = result.quiz;
                this.loadQuestion(0);
            } else {
                this.showAlert(result.message || 'Failed to start quiz!', 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Load question
    async loadQuestion(questionIndex) {
        try {
            const response = await fetch(`api/get_question.php?quiz_id=${this.currentQuiz.id}&question=${questionIndex}`);
            const result = await response.json();

            if (result.success) {
                this.renderQuestion(result.question, questionIndex);
                this.startTimer(result.question.time_per_question);
            } else {
                this.showAlert('Failed to load question!', 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Render question
    renderQuestion(question, questionIndex) {
        const questionContainer = document.getElementById('questionContainer');
        if (!questionContainer) return;

        const progress = ((questionIndex + 1) / this.currentQuiz.total_questions) * 100;

        questionContainer.innerHTML = `
            <div class="question-card fade-in">
                <div class="question-number">Question ${questionIndex + 1} of ${this.currentQuiz.total_questions}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${progress}%"></div>
                </div>
                <div class="question-text">${question.question_text}</div>
                <div class="options">
                    <div class="option" data-option="A">
                        <div class="option-letter">A</div>
                        <div class="option-text">${question.option_a}</div>
                    </div>
                    <div class="option" data-option="B">
                        <div class="option-letter">B</div>
                        <div class="option-text">${question.option_b}</div>
                    </div>
                    <div class="option" data-option="C">
                        <div class="option-letter">C</div>
                        <div class="option-text">${question.option_c}</div>
                    </div>
                    <div class="option" data-option="D">
                        <div class="option-letter">D</div>
                        <div class="option-text">${question.option_d}</div>
                    </div>
                </div>
            </div>
        `;
    }

    // Select option
    selectOption(optionElement) {
        // Remove previous selection
        document.querySelectorAll('.option').forEach(opt => {
            opt.classList.remove('selected');
        });

        // Add selection to clicked option
        optionElement.classList.add('selected');

        // Submit answer after a short delay
        setTimeout(() => {
            this.submitAnswer(optionElement.dataset.option);
        }, 500);
    }

    // Submit answer
    async submitAnswer(selectedOption) {
        const sessionCode = new URLSearchParams(window.location.search).get('session');
        if (!sessionCode) return;

        try {
            const response = await fetch('api/submit_answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_code: sessionCode,
                    question_index: this.currentQuestion,
                    answer: selectedOption
                })
            });

            const result = await response.json();

            if (result.success) {
                this.answers.push({
                    question: this.currentQuestion,
                    answer: selectedOption,
                    correct: result.correct,
                    correct_answer: result.correct_answer
                });

                if (result.correct) {
                    this.score++;
                }

                this.showAnswerFeedback(result.correct, result.correct_answer);
                
                // Move to next question after showing feedback
                setTimeout(() => {
                    this.nextQuestion();
                }, 2000);
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Show answer feedback
    showAnswerFeedback(isCorrect, correctAnswer) {
        document.querySelectorAll('.option').forEach(option => {
            const optionLetter = option.dataset.option;
            
            if (optionLetter === correctAnswer) {
                option.classList.add('correct');
            } else if (option.classList.contains('selected') && !isCorrect) {
                option.classList.add('incorrect');
            }
            
            option.style.pointerEvents = 'none';
        });

        // Show feedback message
        const feedback = document.createElement('div');
        feedback.className = `alert ${isCorrect ? 'alert-success' : 'alert-danger'}`;
        feedback.innerHTML = isCorrect ? 
            '<strong>Correct!</strong> Well done!' : 
            `<strong>Incorrect!</strong> The correct answer was ${correctAnswer}.`;
        
        document.getElementById('questionContainer').appendChild(feedback);
    }

    // Next question
    nextQuestion() {
        this.currentQuestion++;
        
        if (this.currentQuestion < this.currentQuiz.total_questions) {
            this.loadQuestion(this.currentQuestion);
        } else {
            this.finishQuiz();
        }
    }

    // Finish quiz
    async finishQuiz() {
        const sessionCode = new URLSearchParams(window.location.search).get('session');
        if (!sessionCode) return;

        try {
            const response = await fetch('api/finish_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_code: sessionCode,
                    score: this.score,
                    total: this.currentQuiz.total_questions
                })
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = `result.php?session=${sessionCode}`;
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    // Start timer
    startTimer(seconds) {
        this.timeLeft = seconds;
        this.updateTimerDisplay();

        this.timer = setInterval(() => {
            this.timeLeft--;
            this.updateTimerDisplay();

            if (this.timeLeft <= 0) {
                clearInterval(this.timer);
                this.timeUp();
            }
        }, 1000);
    }

    // Update timer display
    updateTimerDisplay() {
        const timerElement = document.getElementById('timer');
        if (!timerElement) return;

        timerElement.textContent = this.timeLeft;
        
        if (this.timeLeft <= 5) {
            timerElement.classList.add('warning');
        } else {
            timerElement.classList.remove('warning');
        }
    }

    // Time up
    timeUp() {
        // Auto-submit if no answer selected
        const selectedOption = document.querySelector('.option.selected');
        if (!selectedOption) {
            this.submitAnswer(''); // Submit empty answer
        }
    }

    // Show alert
    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer') || document.body;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} fade-in`;
        alert.innerHTML = message;
        
        alertContainer.insertBefore(alert, alertContainer.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    // Logout
    async logout() {
        try {
            const response = await fetch('api/logout.php', {
                method: 'POST'
            });

            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'index.php';
            }
        } catch (error) {
            console.error('Logout error:', error);
        }
    }
}

// Initialize the platform when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.quizPlatform = new QuizPlatform();
    window.quizPlatform.init();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuizPlatform;
}
