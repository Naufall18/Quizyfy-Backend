# Quizyfy Backend

REST API backend for the [Quizyfy](https://github.com/Naufall18/Quizyfy) online exam platform, built with Laravel 12 and secured with Laravel Sanctum.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-FF2D20?style=flat)](https://laravel.com/docs/sanctum)
[![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)

---

## Features

- Role-based access control: `admin`, `guru`, `user` (siswa)
- Token authentication via Laravel Sanctum
- Google OAuth 2.0 login (verify ID token via Google tokeninfo)
- Exam management: create, update, delete, start, finish, auto-score
- Question bank with multiple choice, essay, and true/false types
- Batch answer submission with `updateOrCreate` (idempotent)
- Auto-calculate score on exam finish
- Subscription and plan management
- Consistent JSON responses via `BaseResponse` helper

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Laravel 12.x

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/Naufall18/Quizyfy-Backend.git
cd Quizyfy-Backend

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Set database credentials in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quizyfy
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations and seed test data
php artisan migrate --seed

# 6. Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

---

## Seeded Test Data

Running `php artisan migrate --seed` creates:

| Role | Email | Password |
|---|---|---|
| Admin | admin@quizyfy.com | password123 |
| Guru | guru1@quizyfy.com | password123 |
| Guru | guru2@quizyfy.com | password123 |
| Siswa | siswa1@quizyfy.com | password123 |
| Siswa | siswa2@quizyfy.com | password123 |
| Siswa | siswa3@quizyfy.com | password123 |

Also seeds: 5 categories, 2 active exams, 10 questions (multiple choice + essay + true/false).

---

## API Reference

All endpoints are prefixed with `/api`. Protected endpoints require `Authorization: Bearer {token}`.

### Authentication (Public)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/login` | Login with email and password |
| POST | `/register` | Register new account (`role`: user/guru/admin) |
| POST | `/auth/google` | Login or register via Google ID token |
| POST | `/forgot-password` | Request password reset *(stub)* |

### Common (Authenticated)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/dashboard` | Role-based dashboard stats |
| POST | `/logout` | Revoke current token |
| POST | `/change-password` | Change password |

### Guru Endpoints (`role:guru`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/guru/profile` | Get guru profile |
| PUT | `/guru/profile` | Update guru profile |
| GET | `/guru/exams` | List all exams created by guru |
| POST | `/guru/exams` | Create new exam |
| PUT | `/guru/exams/{id}` | Update exam |
| DELETE | `/guru/exams/{id}` | Delete exam (policy: must be owner) |
| GET | `/guru/bank-soal` | List question bank |
| POST | `/guru/exams/{exam}/questions` | Add question to exam |

### Siswa Endpoints (`role:user`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/user/profile` | Get siswa profile |
| GET | `/user/exams` | List available exams |
| POST | `/user/exam/join` | Join exam by token |
| POST | `/user/exams/{exam}/start` | Start exam session |
| POST | `/user/exams/{exam}/answers` | Submit answers (batch) |
| POST | `/user/exams/{exam}/finish` | Finish exam and calculate score |
| GET | `/user/exams/{exam}/result` | Get exam result |
| GET | `/user/exams/{exam}/status` | Get remaining time |

---

## Response Format

All responses follow a consistent structure:

```json
{
  "success": true,
  "status": 200,
  "message": "Data retrieved successfully",
  "data": { ... }
}
```

Error responses:
```json
{
  "success": false,
  "status": 422,
  "message": "Validation failed",
  "errors": { "email": ["The email field is required."] }
}
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # AuthController, ExamController, UserExamController, etc.
│   ├── Middleware/      # RoleMiddleware (role-based access)
│   └── Requests/        # FormRequest validation classes
├── Models/              # Eloquent models (User, Exam, Questions, UserAnswer, etc.)
├── Policies/            # ExamPolicy (owner-only update/delete)
├── Helpers/             # BaseResponse, AvatarHelper
└── Services/            # BankSoalService
database/
├── migrations/          # All table migrations
└── seeders/             # UserSeeder, CategorySeeder, ExamSeeder
routes/
└── api.php              # All API routes grouped by role
```

---

## License

MIT License
