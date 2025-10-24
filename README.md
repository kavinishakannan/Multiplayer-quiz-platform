# 🎯 Real-Time Quiz Platform

A complete multiplayer quiz platform built with PHP, MySQL, HTML, CSS, and JavaScript that runs on XAMPP. Features real-time multiplayer quizzes, live leaderboards, timed questions, and an admin panel.

## ✨ Features

- **🔐 User Authentication**: Registration, login, and session management
- **⚡ Real-Time Multiplayer**: Multiple users can join the same quiz simultaneously
- **⏱️ Timed Questions**: Configurable time limits per question with auto-submit
- **🏆 Live Leaderboard**: Real-time score updates and rankings
- **📊 Analytics**: Track user performance and quiz statistics
- **🛠️ Admin Panel**: Create and manage quizzes and questions
- **📱 Responsive Design**: Works on desktop and mobile devices
- **🎮 Interactive UI**: Modern, engaging user interface

## 🚀 Quick Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser

### Installation Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database called `quiz_platform`
   - Import the `database.sql` file or run the SQL commands

3. **Install Project**
   - Copy the `quiz-platform` folder to your XAMPP htdocs directory
   - Path: `C:\xampp\htdocs\quiz-platform\`

4. **Configure Database**
   - Edit `config.php` if needed (default settings should work)
   - Default MySQL settings:
     - Host: localhost
     - Database: quiz_platform
     - Username: root
     - Password: (empty)

5. **Access the Platform**
   - Open browser and go to: `http://localhost/quiz-platform/`
   - Register a new account or login with admin credentials

## 🔑 Default Admin Account

- **Email**: admin@quiz.com
- **Password**: admin123

## 📁 Project Structure

```
quiz-platform/
├── api/                    # API endpoints
│   ├── login.php
│   ├── register.php
│   ├── join_quiz.php
│   ├── get_participants.php
│   ├── get_leaderboard.php
│   ├── start_quiz.php
│   ├── get_question.php
│   ├── submit_answer.php
│   ├── finish_quiz.php
│   └── logout.php
├── config.php              # Database configuration
├── database.sql            # Database schema
├── styles.css              # Main stylesheet
├── script.js               # JavaScript functionality
├── index.php               # Home page
├── login.php               # Login page
├── register.php            # Registration page
├── dashboard.php           # User dashboard
├── quiz_waiting_room.php   # Multiplayer waiting room
├── quiz.php                # Quiz interface
├── result.php              # Results page
├── leaderboard.php         # Global leaderboard
└── admin.php               # Admin panel
```

## 🎮 How to Use

### For Users
1. **Register/Login**: Create an account or login
2. **Browse Quizzes**: View available quizzes on dashboard
3. **Join Quiz**: Click "Join Quiz" to enter waiting room
4. **Wait for Start**: Wait for other players and quiz to begin
5. **Answer Questions**: Select answers within time limit
6. **View Results**: See your score and ranking
7. **Check Leaderboard**: View global rankings

### For Admins
1. **Login**: Use admin credentials
2. **Add Quizzes**: Create new quizzes with title, description, and settings
3. **Add Questions**: Add multiple-choice questions to quizzes
4. **Manage Content**: View and manage existing quizzes
5. **Monitor Stats**: View user statistics and quiz performance

## 🛠️ Technical Details

### Database Schema
- **users**: User accounts and profiles
- **quizzes**: Quiz information and settings
- **questions**: Quiz questions with multiple choice options
- **results**: User quiz results and scores
- **leaderboard**: Cached leaderboard data
- **quiz_sessions**: Active quiz sessions for multiplayer
- **session_participants**: Users in each quiz session

### Real-Time Features
- AJAX polling for live updates
- Session-based multiplayer coordination
- Real-time leaderboard updates
- Live participant tracking

### Security Features
- Password hashing with PHP's password_hash()
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()
- Session management and authentication

## 🎯 Key Features Explained

### Real-Time Multiplayer
- Users join quiz sessions with unique codes
- Waiting room shows all participants
- Quiz starts when admin begins it
- All users see questions simultaneously

### Timed Questions
- Configurable time per question (5-60 seconds)
- Visual countdown timer with warnings
- Auto-submit when time expires
- Immediate feedback on answers

### Live Leaderboard
- Updates after each question
- Shows current rankings during quiz
- Global leaderboard across all quizzes
- User statistics and performance tracking

## 🔧 Customization

### Adding New Question Types
1. Modify database schema in `questions` table
2. Update `get_question.php` API
3. Modify quiz interface in `quiz.php`
4. Update answer submission logic

### Styling Changes
- Edit `styles.css` for visual customization
- Modify color scheme, fonts, and layouts
- Add animations and transitions
- Implement dark mode or themes

### Database Modifications
- Update `database.sql` for schema changes
- Modify `config.php` for connection settings
- Add new tables or fields as needed

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL is running
   - Verify database name and credentials in `config.php`
   - Ensure database exists in phpMyAdmin

2. **Session Issues**
   - Check PHP session configuration
   - Clear browser cookies and cache
   - Restart Apache server

3. **Real-Time Updates Not Working**
   - Check browser console for JavaScript errors
   - Verify AJAX requests are reaching server
   - Check API endpoint responses

4. **Permission Errors**
   - Ensure XAMPP has proper file permissions
   - Check Apache error logs
   - Verify PHP configuration

## 📈 Performance Optimization

- Enable MySQL query caching
- Add database indexes for frequently queried fields
- Implement Redis for session storage
- Use CDN for static assets
- Optimize images and compress CSS/JS

## 🔒 Security Considerations

- Use HTTPS in production
- Implement rate limiting for API endpoints
- Add CSRF protection for forms
- Regular security updates
- Input validation and sanitization

## 📝 License

This project is open source and available under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For issues and questions:
- Check the troubleshooting section
- Review the code comments
- Create an issue in the repository

---

**Built with ❤️ using PHP, MySQL, HTML, CSS & JavaScript**
