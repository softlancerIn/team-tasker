<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\AuthController;

// Auth Controller
Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'loginPage')->name('loginPage');
    Route::post('/login', 'login')->name('login');
    Route::get('/register', 'registerPage')->name('registerPage');
    Route::post('/register', 'register')->name('register');
    Route::get('/forgotPassword', 'forgotPasswordPage')->name('forgotPasswordPage');
    Route::post('/forgotPassword', 'forgotPassword')->name('forgotPassword');
    Route::get('/logout', 'logout')->name('logout');
});

// Todo Controller
Route::middleware('web')->controller(TodoController::class)->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/index', 'index')->name('index');
    Route::get('create', 'create')->name('create');
    Route::post('store', 'store')->name('store');
    Route::get('details/{id}', 'show')->name('details');
    Route::get('edit/{id}', 'edit')->name('edit');
    Route::post('update', 'update')->name('update');
    Route::get('delete/{id}', 'destroy')->name('delete');
});
