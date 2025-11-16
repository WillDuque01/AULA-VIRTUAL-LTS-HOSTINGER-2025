<?php

use App\Http\Controllers\Admin\DataPorterExportController;
use App\Http\Controllers\Api\PlayerEventController;
use App\Http\Controllers\Api\VideoProgressController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvisionerController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\WhatsAppRedirectController;
use App\Http\Livewire\Builder\CourseBuilder;
use App\Http\Livewire\Player;
use App\Livewire\Admin\AssignmentsManager;
use App\Livewire\Admin\BrandingDesigner;
use App\Livewire\Admin\DataPorterHub;
use App\Livewire\Admin\GroupManager;
use App\Livewire\Admin\MessageCenter as AdminMessageCenter;
use App\Livewire\Admin\IntegrationOutbox;
use App\Livewire\Admin\PaymentSimulator;
use App\Livewire\Admin\TierManager;
use App\Livewire\Catalog\CourseCatalog;
use App\Livewire\Student\MessageCenter as StudentMessageCenter;
use App\Livewire\Student\DiscordPracticeBrowser;
use App\Livewire\Professor\DiscordPracticePlanner;
use App\Livewire\Professor\PracticePackagesManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/es');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');
Route::middleware('auth')->get('/whatsapp/redirect', WhatsAppRedirectController::class)->name('whatsapp.redirect');

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

            Route::get('/admin/assignments', AssignmentsManager::class)
                ->middleware('can:manage-settings')
                ->name('admin.assignments');

            Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])
                ->middleware('auth')
                ->name('certificates.show');

            Route::get('/admin/payments/simulator', PaymentSimulator::class)
                ->middleware('can:manage-settings')
                ->name('admin.payments.simulator');

            Route::get('/admin/messages', AdminMessageCenter::class)
                ->middleware('role:teacher_admin|teacher')
                ->name('admin.messages');

            Route::get('/admin/integrations/outbox', IntegrationOutbox::class)
                ->middleware('can:manage-settings')
                ->name('admin.integrations.outbox');

            Route::get('/admin/data-porter', DataPorterHub::class)
                ->name('admin.data-porter');

            Route::get('/admin/data-porter/export', DataPorterExportController::class)
                ->middleware('signed')
                ->name('admin.data-porter.export');

            Route::get('/student/messages', StudentMessageCenter::class)
                ->middleware('role:student_free|student_paid|student_vip')
                ->name('student.messages');

            Route::get('/student/practices', DiscordPracticeBrowser::class)
                ->middleware('role:student_free|student_paid|student_vip')
                ->name('student.discord-practices');

            Route::get('/professor/practices', DiscordPracticePlanner::class)
                ->middleware('role:Profesor|teacher_admin')
                ->name('professor.discord-practices');

            Route::get('/professor/practice-packs', PracticePackagesManager::class)
                ->middleware('role:Profesor|teacher_admin')
                ->name('professor.practice-packs');

            Route::post('/api/video/progress', [VideoProgressController::class, 'store'])->name('api.video.progress');
            Route::post('/api/player/events', PlayerEventController::class)->name('api.player.events');
        });

        require __DIR__.'/auth.php';
    });

Broadcast::routes(['middleware' => ['auth:sanctum']]);
