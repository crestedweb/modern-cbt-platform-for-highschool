# Nigerian School CBT (Computer-Based Test) System - Laravel 12

A comprehensive Computer-Based Testing system built with Laravel 12 for Nigerian schools.

## Features

### Student Features
- Login with registration number or email
- Dashboard with assigned exams
- Take exams with multiple question types:
  - Multiple Choice (A/B/C/D)
  - Theory/Essay (manual grading)
  - Coding (in-browser code editor)
  - Fill-in-the-blank
- Exam timer with countdown
- Auto-save answers every 30 seconds
- Auto-submit when time expires
- View results and feedback

### Teacher/Admin Features
- Secure login system
- Create and manage exams
- Add questions with different types
- Assign exams to specific classes
- Manual grading interface for theory/coding questions
- Export results as PDF
- Export results as Word document
- Print individual exam scripts
- Analytics dashboard:
  - Average scores
  - Highest and lowest scores
  - Pass rate
  - Class performance

## Technical Stack
- **Framework**: Laravel 12 (Latest)
- **PHP**: ^8.2
- **Database**: MySQL
- **Frontend**: Blade Templates + TailwindCSS + Alpine.js
- **PDF Export**: barryvdh/laravel-dompdf
- **Word Export**: phpoffice/phpword
- **Code Editor**: CodeMirror (CDN)

## Installation

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM (optional, for asset compilation)

### Setup Instructions

1. **Extract the ZIP file**
   ```bash
   unzip nigerian-cbt-system.zip
   cd nigerian-cbt-system
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file and configure your database:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nigerian_cbt
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed Database** (Creates demo users and sample exam)
   ```bash
   php artisan db:seed
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

8. **Access the Application**
   Open your browser and navigate to: `http://localhost:8000`

## Default Login Credentials

After seeding the database, use these credentials:

### Admin Account
- **Email**: admin@school.com
- **Password**: password

### Teacher Accounts
- **Email**: okafor@school.com | **Password**: password
- **Email**: adeyemi@school.com | **Password**: password

### Student Accounts
- **Registration No**: STD2024001 | **Password**: password
- **Registration No**: STD2024002 | **Password**: password
- **Registration No**: STD2024003 | **Password**: password
- **Registration No**: STD2024004 | **Password**: password
- **Registration No**: STD2024005 | **Password**: password

## Usage Guide

### For Students
1. Login with your registration number and password
2. View available exams on your dashboard
3. Click "Start Exam" to begin
4. Answer questions (answers auto-save every 30 seconds)
5. Submit exam before time expires
6. View your results after grading

### For Teachers/Admins
1. Login with email and password
2. Create new exams from the dashboard
3. Add questions to exams (multiple types supported)
4. Assign exams to classes
5. Monitor student attempts
6. Grade subjective questions (theory/coding)
7. Export results as PDF or Word
8. Print individual scripts

## File Structure

```
nigerian-cbt-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── StudentController.php
│   │   │   └── AdminController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Exam.php
│       ├── Question.php
│       ├── ExamAttempt.php
│       ├── Answer.php
│       └── SchoolClass.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── auth/
│       ├── student/
│       ├── admin/
│       └── layouts/
└── routes/
    └── web.php
```

## Database Schema

### Users
- Students, Teachers, and Admins
- Role-based access control

### School Classes
- SS1, SS2, SS3, etc.

### Exams
- Title, subject, duration, marks
- Start and end dates

### Questions
- Multiple types: MCQ, Theory, Coding, Fill-blank
- Linked to exams

### Exam Attempts
- Student submissions
- Scores and grading status

### Answers
- Student responses
- Auto-grading for objective questions
- Manual grading for subjective questions

## Features in Detail

### Auto-Grading
- Multiple choice questions are graded automatically
- Fill-in-the-blank questions are graded automatically (case-insensitive)
- Theory and coding questions require manual grading

### Timer System
- Countdown timer displayed during exam
- Warning when time is running out
- Auto-submit when time expires

### Auto-Save
- Answers saved every 30 seconds
- Manual save on answer change
- Resume exam if interrupted

### Export Features
- **PDF Export**: Formatted results report
- **Word Export**: Editable results document
- **Print Script**: Individual student answer sheets

## Security Features
- CSRF protection on all forms
- Password hashing with bcrypt
- Role-based middleware
- Session management
- SQL injection protection (Eloquent ORM)

## Browser Compatibility
- Chrome (Recommended)
- Firefox
- Safari
- Edge
- Opera

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists: `CREATE DATABASE nigerian_cbt;`

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Composer Install Fails
- Ensure PHP >= 8.2 is installed
- Run: `php -v` to check version
- Update composer: `composer self-update`

## Support & Customization
This system can be customized for your school's specific needs:
- Add more question types
- Custom grading rules
- Additional user roles
- SMS notifications
- Email results
- Student portal features

## License
MIT License

## Credits
Built with Laravel 12 - The PHP Framework for Web Artisans

---
**Version**: 1.0.0  
**Release Date**: December 2025  
**Laravel Version**: 12.x
