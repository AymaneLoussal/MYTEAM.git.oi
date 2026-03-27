<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes (public login/register, logout auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.custom');

// Team routes
Route::middleware('auth.custom')->group(function () {
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::post('/teams/{team}/join', [TeamController::class, 'join']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});

// Admin routes
Route::middleware(['auth.custom', 'admin'])->group(function () {
    Route::get('/admin/users', [TeamController::class, 'users']); // reuse or separate AdminController
});

use App\Http\Controllers\InvitationController;

Route::middleware('auth.custom')->group(function () {

    Route::post('/teams/{id}/invite', [InvitationController::class, 'send']);

    Route::get('/invitations', [InvitationController::class, 'index']);

    Route::post('/invitations/{id}/accept', [InvitationController::class, 'accept']);

    Route::post('/invitations/{id}/reject', [InvitationController::class, 'reject']);
});