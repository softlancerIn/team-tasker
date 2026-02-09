<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TaskLogController;

// Auth Controller
Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'loginPage')->name('loginPage');
    Route::post('/login', 'login')->name('login');
    Route::get('/register', 'registerPage')->name('registerPage');
    Route::post('/register', 'register')->name('register');
    Route::get('/forgotPassword', 'forgotPasswordPage')->name('forgotPasswordPage');
    Route::post('/forgotPassword', 'forgotPassword')->name('forgotPassword');
    Route::get('/reset-password', 'resetPasswordPage')->name('resetPasswordPage');
    Route::post('/reset-password', 'resetPassword')->name('resetPassword');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('/profile/update', 'updateProfile')->name('profile.update');
});

// Search Controller
Route::middleware(['web', 'auth'])->get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.global');

// Task Controller
Route::middleware('web')->controller(TaskController::class)->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard')->middleware('permission:dashboard');
    Route::get('/index', 'index')->name('index')->middleware('permission:tasks.view');
    Route::get('create', 'create')->name('create')->middleware('permission:tasks.create');
    Route::post('store', 'store')->name('store')->middleware('permission:tasks.create');
    Route::get('details/{id}', 'show')->name('details')->middleware('permission:tasks.view');
    Route::get('edit/{id}', 'edit')->name('edit')->middleware('permission:tasks.create'); // Usually permission to edit is tied to create or separate
    Route::post('update', 'update')->name('update')->middleware('permission:tasks.create');
    Route::get('delete/{id}', 'destroy')->name('delete')->middleware('permission:tasks.create'); // Usually permission to delete
});

// Team Management
Route::middleware('web')->controller(TeamController::class)->prefix('admin')->group(function () {
    // Users
    Route::group(['middleware' => 'permission:users.view'], function () {
        Route::get('/users', 'index')->name('admin.users.index');
    });
    Route::group(['middleware' => 'permission:users.create'], function () {
        Route::post('/users/store', 'storeUser')->name('admin.users.store');
    });
    Route::group(['middleware' => 'permission:users.edit'], function () {
        Route::post('/users/{id}/update', 'updateUser')->name('admin.users.update');
        Route::post('/users/{id}/toggle-approval', 'toggleApproval')->name('admin.users.toggleApproval'); // Using edit for approval
    });
    Route::group(['middleware' => 'permission:users.delete'], function () {
        Route::delete('/users/{id}/delete', 'deleteUser')->name('admin.users.delete');
    });
    
    // Roles
    Route::group(['middleware' => 'permission:roles.view'], function () {
        Route::get('/roles', 'roles')->name('admin.roles.index');
    });
    Route::group(['middleware' => 'permission:roles.create'], function () {
        Route::post('/roles/store', 'storeRole')->name('admin.roles.store');
    });
    Route::group(['middleware' => 'permission:roles.edit'], function () {
        Route::post('/roles/{id}/update', 'updateRole')->name('admin.roles.update');
    });
    Route::group(['middleware' => 'permission:roles.delete'], function () {
        Route::delete('/roles/{id}/delete', 'deleteRole')->name('admin.roles.delete');
    });
});

// Settings (Statuses, etc.)
Route::middleware(['web', 'permission:settings'])->controller(App\Http\Controllers\StatusController::class)->prefix('admin/settings')->group(function () {
    Route::get('/statuses', 'index')->name('admin.statuses.index');
    Route::post('/statuses/store', 'store')->name('admin.statuses.store');
    Route::post('/statuses/{id}/update', 'update')->name('admin.statuses.update');
    Route::delete('/statuses/{id}/delete', 'destroy')->name('admin.statuses.delete');
});

// Task Logs & Messaging & Time Tracking
Route::middleware(['web', 'auth'])->controller(TaskLogController::class)->group(function () {
    Route::post('/tasks/{id}/log', 'storeLog')->name('tasks.log');
    Route::post('/tasks/{id}/message', 'sendMessage')->name('tasks.message');
    Route::post('/tasks/{id}/start-timer', 'startTime')->name('tasks.start_timer');
    Route::post('/tasks/{id}/stop-timer', 'stopTime')->name('tasks.stop_timer');
    Route::post('/tasks/{id}/progress', 'updateProgress')->name('tasks.progress');
});
