<?php

use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationIndexController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return route('login') ? redirect()->route('login') : view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, ShowForgotPasswordForm::class])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:auth');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin'])->name('auth.firebase');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/settings', [NotificationSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [NotificationSettingsController::class, 'update'])->name('settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    Route::get('/notifications', [NotificationIndexController::class, '__invoke'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationIndexController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationIndexController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::resource('tasks', TaskController::class);

    Route::get('/trash', [TaskController::class, 'trash'])->name('tasks.trash');
    Route::patch('/tasks/{task}/restore', [TaskController::class, 'restore'])->name('tasks.restore')->withTrashed();
    Route::delete('/tasks/{task}/force', [TaskController::class, 'forceDelete'])->name('tasks.force-delete')->withTrashed();

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');
    });
});
