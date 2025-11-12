<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvisionerController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\Api\VideoProgressController;
use App\Http\Livewire\Builder\CourseBuilder;
use App\Http\Livewire\Player;
use App\Livewire\Admin\GroupManager;
use App\Livewire\Admin\TierManager;
use App\Livewire\Catalog\CourseCatalog;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/setup', SetupController::class)->name('setup');
Route::get('/catalog', CourseCatalog::class)->name('catalog');

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

    // Notificaciones basicas (placeholder UI)
    Route::view('/admin/notifications','notifications.index')->middleware('can:manage-settings');
    Route::view('/student/notifications','notifications.student');

    // Provisioner (Admin)
    Route::view('/provisioner','provisioner.index')
        ->middleware('can:manage-settings')
        ->name('provisioner');
    Route::post('/provisioner/save', [ProvisionerController::class,'save'])
        ->middleware('can:manage-settings')
        ->name('provisioner.save');

    Route::get('/admin/tiers', TierManager::class)
        ->middleware('can:manage-settings')
        ->name('admin.tiers');

    Route::get('/admin/groups', GroupManager::class)
        ->middleware('can:manage-settings')
        ->name('admin.groups');

    Route::post('/api/video/progress', [VideoProgressController::class,'store'])->name('api.video.progress');
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);

require __DIR__.'/auth.php';
