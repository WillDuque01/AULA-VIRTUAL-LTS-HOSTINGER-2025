<?php

use App\Http\Controllers\Api\VideoProgressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvisionerController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SetupController;
use App\Http\Livewire\Builder\CourseBuilder;
use App\Http\Livewire\Player;
use App\Livewire\Admin\BrandingDesigner;
use App\Livewire\Admin\GroupManager;
use App\Livewire\Admin\MessageCenter as AdminMessageCenter;
use App\Livewire\Admin\TierManager;
use App\Livewire\Catalog\CourseCatalog;
use App\Livewire\Student\MessageCenter as StudentMessageCenter;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/es');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::prefix('{locale}')
    ->whereIn('locale', ['es', 'en'])
    ->group(function (): void {
        Route::get('/', function () {
            return view('welcome');
        })->name('welcome');

        Route::get('/setup', SetupController::class)->name('setup');
        Route::get('/catalog', CourseCatalog::class)->name('catalog');

        Route::get('/dashboard', function () {
            return view('dashboard');
        })->middleware(['auth', 'verified'])->name('dashboard');

        Route::middleware('auth')->group(function (): void {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

            Route::get('/courses/{course}/builder', CourseBuilder::class)->name('courses.builder');
            Route::get('/lessons/{lesson}/player', Player::class)->name('lessons.player');

            Route::view('/admin/notifications', 'notifications.index')
                ->middleware('can:manage-settings');
            Route::view('/student/notifications', 'notifications.student');

            Route::view('/provisioner', 'provisioner.index')
                ->middleware('can:manage-settings')
                ->name('provisioner');
            Route::post('/provisioner/save', [ProvisionerController::class, 'save'])
                ->middleware('can:manage-settings')
                ->name('provisioner.save');

            Route::get('/admin/tiers', TierManager::class)
                ->middleware('can:manage-settings')
                ->name('admin.tiers');

            Route::get('/admin/groups', GroupManager::class)
                ->middleware('can:manage-settings')
                ->name('admin.groups');

            Route::get('/admin/branding', BrandingDesigner::class)
                ->middleware('can:manage-settings')
                ->name('admin.branding');

            Route::get('/admin/messages', AdminMessageCenter::class)
                ->middleware('role:teacher_admin|teacher')
                ->name('admin.messages');

            Route::get('/student/messages', StudentMessageCenter::class)
                ->middleware('role:student_free|student_paid|student_vip')
                ->name('student.messages');

            Route::post('/api/video/progress', [VideoProgressController::class, 'store'])->name('api.video.progress');
        });

        require __DIR__.'/auth.php';
    });

Broadcast::routes(['middleware' => ['auth:sanctum']]);
