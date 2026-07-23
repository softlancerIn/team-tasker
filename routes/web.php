<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskLogController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Auth Controller
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'loginPage')->name('login');
    Route::get('/', 'loginPage')->name('loginPage');
    Route::post('/login', 'login')->name('login_submit');
    Route::get('/register', 'registerPage')->name('registerPage');
    Route::post('/register', 'register')->name('register');

    // Client Registration
    Route::get('/client/register', 'clientRegisterPage')->name('clientRegisterPage');
    Route::post('/client/register', 'clientRegister')->name('clientRegister');

    // OTP Verification
    Route::get('/verify-otp', 'verifyOtpPage')->name('verifyOtpPage');
    Route::post('/verify-otp', 'verifyOtp')->name('verifyOtp');
    Route::post('/resend-otp', 'resendOtp')->name('resendOtp');

    Route::get('/forgotPassword', 'forgotPasswordPage')->name('forgotPasswordPage');
    Route::post('/forgotPassword', 'forgotPassword')->name('forgotPassword');
    Route::get('/reset-password', 'resetPasswordPage')->name('resetPasswordPage');
    Route::post('/reset-password', 'resetPassword')->name('resetPassword');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('/profile/update', 'updateProfile')->name('profile.update');
});

// Search Controller
Route::middleware(['web', 'auth:web,admin'])->get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.global');

// Notifications
Route::middleware(['web', 'auth:web,admin,client'])->group(function () {
    Route::post('/notifications/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/update-fcm-token', function (Illuminate\Http\Request $request) {
        $request->validate(['token' => 'required|string']);
        $user = auth('client')->user() ?? auth('web')->user() ?? auth('admin')->user();
        if ($user) {
            $tokens = json_decode($user->fcm_token, true);
            if (!is_array($tokens)) {
                $tokens = $user->fcm_token ? [$user->fcm_token] : [];
            }
            if (!in_array($request->token, $tokens)) {
                $tokens[] = $request->token;
                if (count($tokens) > 5) {
                    $tokens = array_slice($tokens, -5);
                }
                $user->fcm_token = json_encode($tokens);
                $user->save();
            }

            // Sync with counterpart
            if (!$user instanceof \App\Models\Client) {
                $syncTokens = json_encode($tokens);
                if ($user instanceof \App\Models\Admin) {
                    \App\Models\User::where('email', $user->email)->update(['fcm_token' => $syncTokens]);
                } elseif ($user instanceof \App\Models\User) {
                    \App\Models\Admin::where('email', $user->email)->update(['fcm_token' => $syncTokens]);
                }
            }
        }

        return response()->json(['success' => true]);
    })->name('update.fcm_token');
});

// Chunk Upload Route
Route::post('/upload-chunk', [App\Http\Controllers\ChunkUploadController::class, 'upload'])->name('upload.chunk')->middleware(['web']);

// Public Enquiry Route
Route::get('/enquire', [App\Http\Controllers\EnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/enquire', [App\Http\Controllers\EnquiryController::class, 'store'])->name('enquiry.store');

// Project Controller
Route::middleware(['web', 'auth:web,admin'])->controller(App\Http\Controllers\ProjectController::class)->prefix('admin/projects')->group(function () {
    Route::get('/', 'index')->name('admin.projects.index')->middleware('permission:projects.view');
    Route::get('/create', 'create')->name('admin.projects.create')->middleware('permission:projects.create');
    Route::post('/', 'store')->name('admin.projects.store')->middleware('permission:projects.create');
    Route::get('/{id}', 'show')->name('admin.projects.show')->middleware('permission:projects.view');
    Route::post('/{id}/assign-task', 'assignTask')->name('admin.projects.assignTask')->middleware('permission:projects.edit');
    Route::get('/{id}/edit', 'edit')->name('admin.projects.edit')->middleware('permission:projects.edit');
    Route::post('/{id}/update', 'update')->name('admin.projects.update')->middleware('permission:projects.edit');
    Route::delete('/{id}', 'destroy')->name('admin.projects.destroy')->middleware('permission:projects.delete');
});

// Todo Controller
Route::middleware(['web', 'auth:web,admin', 'permission:tasks.todo'])->get('/admin/todos', [\App\Http\Controllers\TodoController::class, 'index'])->name('admin.todos.index');

// Task Controller
Route::middleware(['web', 'auth:web,admin'])->controller(TaskController::class)->prefix('admin/tasks')->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard')->middleware('permission:dashboard.view');
    Route::get('/api/users/search', 'searchUsers')->name('admin.api.users.search');
    Route::get('/', 'index')->name('index')->middleware('permission:tasks.my_tasks');
    Route::get('activity', 'activity')->name('tasks.activity')->middleware('permission:tasks.activity');
    Route::get('board', 'board')->name('tasks.board')->middleware('permission:tasks.board');
    Route::get('calendar', 'calendar')->name('tasks.calendar')->middleware('permission:tasks.calendar');
    Route::get('events', 'calendarEvents')->name('tasks.calendar.events')->middleware('permission:tasks.calendar');
    Route::get('gantt', 'gantt')->name('tasks.gantt')->middleware('permission:tasks.gantt');
    Route::get('gantt-data', 'ganttData')->name('tasks.gantt.data')->middleware('permission:tasks.gantt');
    Route::get('create', 'create')->name('create')->middleware('permission:tasks.create');
    Route::post('store', 'store')->name('store')->middleware('permission:tasks.create');
    Route::get('details/{id}', 'show')->name('details')->middleware('permission:tasks.view');
    Route::get('edit/{id}', 'edit')->name('edit')->middleware('permission:tasks.edit');
    Route::post('update/{id}', 'update')->name('update')->middleware('permission:tasks.edit');
    Route::get('delete/{id}', 'destroy')->name('delete')->middleware('permission:tasks.delete');
});

// Team Management
Route::middleware(['web', 'auth:web,admin'])->controller(TeamController::class)->prefix('admin')->group(function () {
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

    Route::post('/users/bulk-action', 'bulkAction')->name('admin.users.bulkAction');

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

    // Chat Route
    Route::get('/chat', function () {
        return view('admin.chat.index');
    })->name('admin.chat.index')->middleware('permission:chat.view');

    // Tickets
    Route::controller(App\Http\Controllers\TicketController::class)->prefix('tickets')->group(function () {
        Route::get('/', 'index')->name('admin.tickets.index')->middleware('permission:tickets.view');
        Route::get('/create', 'create')->name('admin.tickets.create')->middleware('permission:tickets.create');
        Route::post('/', 'store')->name('admin.tickets.store')->middleware('permission:tickets.create');
        Route::get('/{id}', 'show')->name('admin.tickets.show')->middleware('permission:tickets.view');
        Route::post('/{id}/update', 'updateStatus')->name('admin.tickets.update')->middleware('permission:tickets.edit');
        Route::post('/{id}/reply', 'storeReply')->name('admin.tickets.reply')->middleware('permission:tickets.reply');
        Route::post('/{id}/assign', 'assign')->name('admin.tickets.assign')->middleware('permission:tickets.assign');
        Route::post('/{id}/convert-to-task', 'convertToTask')->name('admin.tickets.convert_to_task')->middleware('permission:tasks.create');
    });

    // Clients
    Route::controller(App\Http\Controllers\AdminClientController::class)->prefix('clients')->group(function () {
        Route::get('/', 'index')->name('admin.clients.index')->middleware('permission:clients.view');
        Route::get('/create', 'create')->name('admin.clients.create')->middleware('permission:clients.create');
        Route::post('/', 'store')->name('admin.clients.store')->middleware('permission:clients.create');
        Route::get('/{id}/edit', 'edit')->name('admin.clients.edit')->middleware('permission:clients.edit');
        Route::post('/{id}/update', 'update')->name('admin.clients.update')->middleware('permission:clients.edit');
        Route::delete('/{id}/delete', 'destroy')->name('admin.clients.delete')->middleware('permission:clients.delete');
    });
});

// Client Support Portal
Route::middleware(['web', 'auth:client,admin'])->prefix('client')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClientController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/tickets/create', [App\Http\Controllers\ClientController::class, 'create'])->name('client.tickets.create');
    Route::post('/tickets', [App\Http\Controllers\ClientController::class, 'store'])->name('client.tickets.store');
    Route::get('/tickets/{id}', [App\Http\Controllers\ClientController::class, 'show'])->name('client.tickets.show');
    Route::post('/tickets/{id}/reply', [App\Http\Controllers\ClientController::class, 'reply'])->name('client.tickets.reply');

    // Client Tasks
    Route::get('/tasks/{id}', [App\Http\Controllers\ClientController::class, 'showTask'])->name('client.tasks.show');
    Route::post('/tasks/{id}/reply', [App\Http\Controllers\ClientController::class, 'replyTask'])->name('client.tasks.reply');

    // Client Chat
    Route::get('/chat', function () {
        return view('client.chat.index');
    })->name('client.chat.index');

    // Client Profile Update
    Route::post('/profile/update', [App\Http\Controllers\ClientController::class, 'updateProfile'])->name('client.profile.update');

    // Client Notifications
    Route::post('/notifications/mark-as-read', [App\Http\Controllers\ClientController::class, 'markNotificationsRead'])->name('client.notifications.markAsRead');
});

// Attendance Management
Route::middleware(['web', 'auth:web,admin'])->controller(App\Http\Controllers\AttendanceController::class)->prefix('admin/attendance')->group(function () {
    Route::get('/', 'dashboard')->name('admin.attendance.dashboard');
    Route::post('/clock-in', 'clockIn')->name('admin.attendance.clockIn');
    Route::post('/clock-out', 'clockOut')->name('admin.attendance.clockOut');
    Route::get('/requests', 'requests')->name('admin.attendance.requests');
    Route::post('/requests', 'storeRequest')->name('admin.attendance.requests.store');
    Route::put('/requests/{id}', 'updateRequest')->name('admin.attendance.requests.update');
    Route::get('/calendar', 'calendar')->name('admin.attendance.calendar');
    
    // Admin Only Attendance Routes
    Route::get('/daily', 'daily')->name('admin.attendance.daily')->middleware('permission:attendance.daily');
    Route::post('/daily/update', 'updateDailyAttendance')->name('admin.attendance.daily.update')->middleware('permission:attendance.daily');
    Route::get('/monthly', 'monthly')->name('admin.attendance.monthly')->middleware('permission:attendance.monthly');
    Route::put('/requests/{id}/status', 'updateRequestStatus')->name('admin.attendance.requests.updateStatus')->middleware('permission:attendance.requests_manage');
    Route::get('/reports', 'reports')->name('admin.attendance.reports')->middleware('permission:attendance.reports');
    Route::get('/settings', 'settings')->name('admin.attendance.settings')->middleware('permission:attendance.settings');
    Route::put('/settings', 'updateSettings')->name('admin.attendance.settings.update')->middleware('permission:attendance.settings');
});

// Consolidated Settings
Route::middleware(['web', 'auth:web,admin'])->controller(App\Http\Controllers\SettingsController::class)->prefix('admin/settings')->group(function () {
    Route::get('/general', 'general')->name('admin.settings.general')->middleware('permission:settings.view');
    Route::get('/statuses', 'statuses')->name('admin.settings.statuses')->middleware('permission:settings.view');
    Route::get('/email', 'email')->name('admin.settings.email')->middleware('permission:settings.view');
    Route::get('/autostop', 'autostop')->name('admin.settings.autostop')->middleware('permission:settings.view');

    Route::view('/chat-permissions', 'admin.settings.chat-permissions')->name('admin.chat-permissions')->middleware('permission:settings.view');

    // General
    Route::post('/general', 'storeGeneral')->name('admin.settings.general.store')->middleware('permission:settings.edit');

    // Email
    Route::post('/email', 'storeEmail')->name('admin.settings.email.store')->middleware('permission:settings.edit');

    // Auto Stop Timer
    Route::post('/autostop', 'storeAutostop')->name('admin.settings.autostop.store')->middleware('permission:settings.edit');

    // Statuses
    Route::post('/statuses', 'storeStatus')->name('admin.settings.status.store')->middleware('permission:settings.edit');
    Route::post('/statuses/{id}/update', 'updateStatus')->name('admin.settings.status.update')->middleware('permission:settings.edit');
    Route::delete('/statuses/{id}/delete', 'destroyStatus')->name('admin.settings.status.delete')->middleware('permission:settings.edit');

    // Tags
    Route::get('/tags', 'tags')->name('admin.settings.tags')->middleware('permission:settings.view');
    Route::post('/tags', 'storeTag')->name('admin.settings.tag.store')->middleware('permission:settings.edit');
    Route::post('/tags/{id}/update', 'updateTag')->name('admin.settings.tag.update')->middleware('permission:settings.edit');
    Route::delete('/tags/{id}/delete', 'destroyTag')->name('admin.settings.tag.delete')->middleware('permission:settings.edit');
});

// Task Logs & Messaging & Time Tracking
Route::middleware(['web', 'auth:web,admin'])->controller(TaskLogController::class)->group(function () {
    Route::post('/tasks/{id}/log', 'storeLog')->name('tasks.log')->middleware('permission:tasks.view');
    Route::post('/tasks/{id}/message', 'sendMessage')->name('tasks.message')->middleware('permission:tasks.view');
    Route::post('/tasks/{id}/start-timer', 'startTime')->name('tasks.start_timer')->middleware('permission:tasks.view');
    Route::post('/tasks/{id}/stop-timer', 'stopTime')->name('tasks.stop_timer')->middleware('permission:tasks.view');
    Route::post('/tasks/{id}/progress', 'updateProgress')->name('tasks.progress')->middleware('permission:tasks.view');
});

// Dynamic Service Worker Route
Route::get('/firebase-messaging-sw.js', function () {
    return response()->view('firebase-messaging-sw')
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
})->name('firebase.sw');
