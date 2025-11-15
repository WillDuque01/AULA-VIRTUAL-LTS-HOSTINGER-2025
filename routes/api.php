<?php

use App\Http\Controllers\Api\CertificateVerificationController;
use App\Http\Controllers\Api\VideoProgressController;
use App\Http\Controllers\Webhooks\MakeWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/video/progress', [VideoProgressController::class, 'store']);
});

Route::post('/webhooks/make', MakeWebhookController::class)->name('webhooks.make');

Route::get('/certificates/verify', [CertificateVerificationController::class, 'show'])
    ->name('api.certificates.verify');
