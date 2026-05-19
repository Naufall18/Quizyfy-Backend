<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserExamController;
use App\Http\Controllers\UserAnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini memakai:
| - auth:sanctum
| - throttle:api (60 req/min per user/IP)
|
*/

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by(optional($request->user())->id ?: $request->ip());
});

Route::middleware('throttle:api')->group(function () {
    // Public endpoints (no auth)
    Route::post('/login',            [AuthController::class, 'login']);
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',   [AuthController::class, 'resetPassword']);
    
    // Protected: must be authenticated via Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        // Common user actions
        Route::get('/user',             [AuthController::class, 'user']); // Tambahan ini
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::put('/update-password',  [AuthController::class, 'update']);
        Route::post('/logout',          [AuthController::class, 'logout']);

        // Dashboard umum (berdasarkan role: admin, guru, siswa)
        Route::get('dashboard',          [DashboardController::class, 'index'])
             ->name('dashboard.index');

        // Detail satu exam pada dashboard (role-based)
        Route::get('dashboard/{exam}',   [DashboardController::class, 'show'])
             ->whereNumber('exam')
             ->name('dashboard.show');

        // 1. Admin-only
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Guru management
            Route::get('gurus',        [AdminController::class, 'index']);
            Route::get('gurus/{id}',   [AdminController::class, 'show']);
            // Transaction history
            Route::get('history',      [AdminController::class, 'history']);
            // Finance overview
            Route::get('finance',      [AdminController::class, 'keuangan']);
            // Subscriptions
            Route::get('subscriptions',                   [SubscriptionController::class,'index']);
            Route::put('subscriptions/{subscription}',    [SubscriptionController::class,'update']);
            Route::get('subscriptions/{subscription}',    [SubscriptionController::class,'show']);
            // System settings
            Route::get('settings',     [SystemSettingController::class, 'index']);
            Route::put('settings',     [SystemSettingController::class, 'update']);
            // Dashboard
            Route::get('dashboard',    [DashboardController::class, 'index']);
            // Audit logs
            Route::get('audit-logs',   [DashboardController::class, 'auditLogs']);
        });

        // 2. Guru-only
        Route::middleware('role:guru')->prefix('guru')->group(function () {
            // Bank soal
            Route::get('bank-soal',                 [QuestionController::class, 'bank']);
            Route::post   ('questions/attach', [QuestionController::class,'attachToExam']);
            Route::delete ('questions/{question}', [QuestionController::class,'detach']);

            // Profile
            Route::get('profile',                   [GuruController::class, 'index']);
            Route::get('profile/{id}',              [GuruController::class, 'show']);
            Route::put('profile',                   [GuruController::class, 'update']);
            Route::post('profile/avatar',           [GuruController::class, 'updateAvatar']);
            // Exams & Questions
            Route::apiResource('exams',            ExamController::class);
            Route::apiResource('exams.questions',  QuestionController::class);
            // My subscriptions
            Route::get('subscriptions',             [SubscriptionController::class,'subscriptions']);
            Route::get('plans',             [SubscriptionController::class,'plan']);
            Route::post('subscriptions',            [SubscriptionController::class,'store']);
            // Categories
            Route::apiResource('categories',       CategoryController::class)
                 ->shallow();
        });

        // 3. User-only (siswa)
        Route::middleware('role:user')->prefix('user')->group(function () {
            // Profile
            Route::get('profile',                  [SiswaController::class, 'index']);
            Route::get('profile/{id}',             [SiswaController::class, 'show']);
            Route::put('profile',                  [SiswaController::class, 'update']);
            Route::post('profile/avatar',          [SiswaController::class, 'updateAvatar']);
            // Categories
            Route::get('categories',               [CategoryController::class, 'indexActive']);
            Route::get('categories/{category:slug}', [CategoryController::class, 'showBySlug']);
            Route::get('categories/{categories}',  [CategoryController::class, 'show']);
            // Exams
            Route::post('exam/join',[ExamController::class, 'examJoin']);
            Route::get('exams',                    [ExamController::class, 'available']);
            Route::get('exam/{exam}',              [ExamController::class, 'show']);
            Route::get('exam',                     [ExamController::class, 'index']);
            // Exam behavior
            Route::post('exams/{exam}/start',      [UserExamController::class, 'start']);
            Route::get('exams/{exam}/status',      [UserExamController::class, 'status']);
            Route::post('exams/{exam}/finish',     [UserExamController::class, 'finish']);
            Route::get('exams/{exam}/result',      [UserExamController::class, 'result']);

            // Submit answers
            Route::post('exams/{exam}/answers',    [UserAnswerController::class, 'store']);
            // Questions listing
            Route::get('questions',                [QuestionController::class, 'index']);
            Route::get('questions/{question}',     [QuestionController::class, 'show']);
            // Options resource
            Route::apiResource('options',          SystemSettingController::class);
            // Bank soal for practice
            Route::get('bank-soal',                [QuestionController::class, 'bank']);
        });
    });
});