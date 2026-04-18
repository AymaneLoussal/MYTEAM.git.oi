<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

// Note: This project uses custom session auth. API routes are not enabled by default
// in bootstrap/app.php. If you enable them, ensure the "web" middleware (sessions)
// is applied or keep using routes/web.php.

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.custom');

Route::middleware('auth.custom')->group(function () {
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::post('/teams/{team}/join', [TeamController::class, 'join']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::post('/teams/{team}/invite', [InvitationController::class, 'send']);
    Route::get('/invitations', [InvitationController::class, 'index']);
    Route::match(['post', 'patch'], '/invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::match(['post', 'patch'], '/invitations/{token}/reject', [InvitationController::class, 'reject']);

    Route::get('/trainings', [TrainingController::class, 'index']);
    Route::get('/teams/{team}/trainings', [TrainingController::class, 'team']);
    Route::post('/teams/{team}/trainings', [TrainingController::class, 'store']);
    Route::patch('/trainings/{training}', [TrainingController::class, 'update']);
    Route::delete('/trainings/{training}', [TrainingController::class, 'destroy']);

    Route::get('/matches', [MatchController::class, 'index']);
    Route::get('/teams/{team}/matches', [MatchController::class, 'team']);
    Route::post('/teams/{team}/matches', [MatchController::class, 'store']);
    Route::get('/matches/{match}', [MatchController::class, 'show']);
    Route::patch('/matches/{match}', [MatchController::class, 'update']);
    Route::patch('/matches/{match}/lineup', [MatchController::class, 'updateLineup']);
    Route::delete('/matches/{match}', [MatchController::class, 'destroy']);
});

Route::middleware(['auth.custom', 'admin'])->group(function () {
    Route::get('/admin/users', [TeamController::class, 'users']);
});
