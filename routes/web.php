<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Livewire\Builder\CourseBuilder;
use App\Http\Livewire\Player;
use App\Http\Controllers\Api\VideoProgressController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\ProvisionerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Builder y Player
    Route::get('/courses/{course}/builder', CourseBuilder::class)->name('courses.builder');
    Route::get('/lessons/{lesson}/player', Player::class)->name('lessons.player');

    // Notificaciones básicas (placeholder UI)
    Route::view('/admin/notifications','notifications.index')->middleware('can:manage-settings');
    Route::view('/student/notifications','notifications.student');

    // Provisioner (Admin)
    Route::view('/provisioner','provisioner.index')
        ->middleware('can:manage-settings')
        ->name('provisioner');
    Route::post('/provisioner/save', [ProvisionerController::class,'save'])
        ->middleware('can:manage-settings')
        ->name('provisioner.save');

    // API progreso de video (usa sesión web)
    Route::post('/api/video/progress', [VideoProgressController::class,'store'])->name('api.video.progress');
});

require __DIR__.'/auth.php';

// Socialite Google
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
