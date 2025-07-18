<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/activate/{user}', [RegisterController::class, 'activate'])->name('activate')->middleware('signed');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/2fa', [LoginController::class, 'showTwoFactorForm'])->name('2fa.form');
Route::post('/2fa', [LoginController::class, 'verifyTwoFactor'])->name('2fa.verify');

// Rutas para restablecimiento de contraseña
Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'sendResetCode'])->name('password.email');
Route::get('/verify-code', [LoginController::class, 'showVerifyCodeForm'])->name('password.verify.form');
Route::post('/verify-code', [LoginController::class, 'verifyResetCode'])->name('password.verify');
Route::get('/reset-password', [LoginController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [LoginController::class, 'updatePassword'])->name('password.update');
Route::post('/resend-code', [LoginController::class, 'resendResetCode'])->name('password.resend');

