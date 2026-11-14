<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login.form');

Route::get('/register', function () {
    return view('auth.register');
})->name('register.form');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration')->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.custom')->name('logout');

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::match(['post', 'patch'], '/invitations/{token}/accept', [InvitationController::class, 'accept'])->middleware('throttle:60,1')->name('invitations.accept');
Route::post('/invitations/{token}/register-and-accept', [InvitationController::class, 'registerAndAccept'])->middleware('throttle:registration')->name('invitations.register');
Route::match(['post', 'patch'], '/invitations/{token}/reject', [InvitationController::class, 'reject'])->middleware('auth.custom')->name('invitations.reject');

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/teams', [DashboardController::class, 'createTeam'])->name('dashboard.teams.create');
    Route::post('/dashboard/teams/{team}/invite', [DashboardController::class, 'sendInvite'])->middleware('throttle:invitations')->name('dashboard.teams.invite');

    Route::get('/trainings', [TrainingController::class, 'index'])->name('trainings.index');
    Route::get('/teams/{team}/trainings', [TrainingController::class, 'team'])->name('trainings.team');
    Route::get('/teams/{team}/trainings/create', [TrainingController::class, 'create'])->name('trainings.create');
    Route::post('/teams/{team}/trainings', [TrainingController::class, 'store'])->name('trainings.store');
    Route::get('/trainings/{training}', [TrainingController::class, 'show'])->name('trainings.show');
    Route::get('/trainings/{training}/edit', [TrainingController::class, 'edit'])->name('trainings.edit');
    Route::patch('/trainings/{training}', [TrainingController::class, 'update'])->name('trainings.update');
    Route::delete('/trainings/{training}', [TrainingController::class, 'destroy'])->name('trainings.destroy');

    // Chat System
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.poll');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/messages', [ChatController::class, 'store'])->middleware('throttle:chat')->name('chat.messages');
    Route::post('/chat/conversations', [ChatController::class, 'create'])->middleware('throttle:chat')->name('chat.create');

    Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
    Route::get('/teams/{team}/formations', [FormationController::class, 'team'])->name('formations.team');
    Route::get('/teams/{team}/formations/create', [FormationController::class, 'create'])->name('formations.create');
    Route::post('/teams/{team}/formations', [FormationController::class, 'store'])->name('formations.store');
    Route::get('/formations/{formation}', [FormationController::class, 'show'])->name('formations.show');
    Route::patch('/formations/{formation}', [FormationController::class, 'update'])->name('formations.update');

    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/teams/{team}/matches', [MatchController::class, 'team'])->name('matches.team');
    Route::get('/teams/{team}/matches/create', [MatchController::class, 'create'])->name('matches.create');
    Route::post('/teams/{team}/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
    Route::patch('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
    Route::get('/matches/{match}/lineup', [MatchController::class, 'lineup'])->name('matches.lineup');
    Route::patch('/matches/{match}/lineup', [MatchController::class, 'updateLineup'])->name('matches.lineup.update');

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams/{team}/join', [TeamController::class, 'join'])->name('teams.join');
    Route::post('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');
    Route::patch('/teams/{team}/players/{player}/notes', [TeamController::class, 'updatePlayerNotes'])->name('teams.update-notes');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/teams/{team}/invite', [InvitationController::class, 'send'])->middleware('throttle:invitations')->name('teams.invite');
    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
});

Route::middleware(['auth.custom', 'admin'])->group(function () {
    Route::get('/admin/users', [TeamController::class, 'users'])->name('admin.users');
});
// Router group block ended
// Admin middleware group stub
