<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\PresenterController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\SuperadminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Participant Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/join', [ParticipantController::class, 'joinPage'])->name('participant.join');
Route::post('/join', [ParticipantController::class, 'joinSubmit'])->name('participant.join.submit');

Route::get('/p/{code}', function (string $code) {
    return redirect()->route('participant.view', ['code' => $code]);
});

Route::get('/m/{code}', [ParticipantController::class, 'viewByCode'])->name('participant.view');
Route::get('/m/{code}/state', [ParticipantController::class, 'getParticipantState'])->name('participant.state');
Route::post('/m/{code}/answer', [ParticipantController::class, 'submitAnswer'])->name('participant.answer');
Route::post('/m/{code}/question', [ParticipantController::class, 'submitQuestion'])->name('participant.question');
Route::post('/m/{code}/question/{question}/upvote', [ParticipantController::class, 'upvoteQuestion'])->name('participant.question.upvote');
Route::post('/m/{code}/profile', [ParticipantController::class, 'saveProfile'])->name('participant.profile');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/demo-login', [AuthController::class, 'quickDemoLogin'])->name('demo.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard & Presentation Studio Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Folders
    Route::post('/folders', [PresentationController::class, 'storeFolder'])->name('folders.store');
    Route::delete('/folders/{folder}', [PresentationController::class, 'destroyFolder'])->name('folders.destroy');

    // Presentations
    Route::post('/presentations', [PresentationController::class, 'store'])->name('presentations.store');
    Route::get('/presentations/{presentation}/edit', [PresentationController::class, 'edit'])->name('presentations.edit');
    Route::put('/presentations/{presentation}', [PresentationController::class, 'update'])->name('presentations.update');
    Route::delete('/presentations/{presentation}', [PresentationController::class, 'destroy'])->name('presentations.destroy');
    Route::post('/presentations/{presentation}/duplicate', [PresentationController::class, 'duplicate'])->name('presentations.duplicate');
    Route::post('/presentations/{presentation}/reset', [PresentationController::class, 'resetResponses'])->name('presentations.reset');
    Route::get('/presentations/{presentation}/export', [PresentationController::class, 'exportCsv'])->name('presentations.export');
    Route::post('/presentations/template', [PresentationController::class, 'createFromTemplate'])->name('presentations.template');

    // Slides
    Route::post('/presentations/{presentation}/slides', [SlideController::class, 'store'])->name('slides.store');
    Route::put('/slides/{slide}', [SlideController::class, 'update'])->name('slides.update');
    Route::delete('/slides/{slide}', [SlideController::class, 'destroy'])->name('slides.destroy');
    Route::post('/slides/{slide}/duplicate', [SlideController::class, 'duplicate'])->name('slides.duplicate');
    Route::post('/presentations/{presentation}/slides/reorder', [SlideController::class, 'reorder'])->name('slides.reorder');
    Route::get('/slides/{slide}/data', [SlideController::class, 'show'])->name('slides.data');
    Route::post('/slides/{slide}/image', [SlideController::class, 'uploadImage'])->name('slides.image.upload');
    Route::post('/slides/{slide}/remove-image', [SlideController::class, 'removeImage'])->name('slides.image.remove');

    // Live Presenter View
    Route::get('/presentations/{presentation}/present', [PresenterController::class, 'present'])->name('presenter.view');
    Route::post('/presentations/{presentation}/slide', [PresenterController::class, 'setCurrentSlide'])->name('presenter.slide');
    Route::post('/presentations/{presentation}/toggle-voting', [PresenterController::class, 'toggleVoting'])->name('presenter.toggle-voting');
    Route::post('/presentations/{presentation}/toggle-results', [PresenterController::class, 'toggleResults'])->name('presenter.toggle-results');
    Route::get('/presentations/{presentation}/live-state', [PresenterController::class, 'getLiveState'])->name('presenter.live-state');
});

/*
|--------------------------------------------------------------------------
| Superadmin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    // Superadmin Authentication Routes
    Route::get('/login', [SuperadminController::class, 'showLogin'])->name('login');
    Route::post('/login', [SuperadminController::class, 'login'])->name('login.submit');
    Route::post('/logout', [SuperadminController::class, 'logout'])->name('logout');

    // Protected Superadmin Panel Routes
    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::get('/', [SuperadminController::class, 'index'])->name('index');
        Route::post('/users', [SuperadminController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{user}/reset-password', [SuperadminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-role', [SuperadminController::class, 'toggleRole'])->name('users.toggle-role');
        Route::delete('/users/{user}', [SuperadminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/features/{setting}/toggle', [SuperadminController::class, 'toggleFeature'])->name('features.toggle');
    });
});
