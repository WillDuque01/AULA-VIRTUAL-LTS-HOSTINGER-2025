<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateVerificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificateVerificationController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $code = $request->query('code');
        if (! $code) {
            return response()->json([
                'valid' => false,
                'message' => __('Falta el parámetro code.'),
            ], 422);
        }

        if (! $this->isAuthorized($request)) {
            Log::warning('Certificate verification signature mismatch');

            return response()->json([
                'valid' => false,
                'message' => __('Firma inválida.'),
            ], 401);
        }

        $certificate = Certificate::with(['user', 'course'])
            ->where('code', $code)
            ->first();

        if (! $certificate) {
            return response()->json([
                'valid' => false,
                'message' => __('No encontramos un certificado con ese código.'),
            ], 404);
        }

        $certificate->increment('verified_count');
        $certificate->forceFill(['last_verified_at' => now()])->save();
        $this->logVerification($certificate, $request, 'api');

        return response()->json([
            'valid' => true,
            'certificate' => [
                'code' => $certificate->code,
                'issued_at' => optional($certificate->issued_at)->toIso8601String(),
                'student' => [
                    'name' => $certificate->user?->name,
                    'email' => $certificate->user?->email,
                ],
                'course' => [
                    'slug' => $certificate->course?->slug,
                ],
            ],
        ]);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = config('services.certificates.verify_secret');
        if (! $secret) {
            return true;
        }

        $provided = $request->header('X-Verify-Signature');

        $payload = $request->query('code');
        $signature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, (string) $provided);
    }

    private function logVerification(Certificate $certificate, Request $request, string $source): void
    {
        CertificateVerificationLog::create([
            'certificate_id' => $certificate->id,
            'source' => $source,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'meta' => [
                'query' => $request->query(),
            ],
        ]);
    }
}


