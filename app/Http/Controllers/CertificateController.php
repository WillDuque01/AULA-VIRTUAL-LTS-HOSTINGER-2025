<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateVerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function show(Request $request, Certificate $certificate)
    {
        abort_unless(Auth::id() === $certificate->user_id, 403);

        if (! Storage::disk('local')->exists($certificate->file_path)) {
            abort(404);
        }

        return response()->file(storage_path('app/'.$certificate->file_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificate-'.$certificate->code.'.pdf"',
        ]);
    }

    public function verify(Request $request, string $code)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('code', $code)
            ->first();

        $shareUrl = null;
        $qrDataUrl = null;

        if ($certificate) {
            $certificate->increment('verified_count');
            $certificate->forceFill(['last_verified_at' => now()])->save();
            $this->logVerification($certificate, $request, 'web');

            $shareUrl = route('certificates.verify', ['code' => $certificate->code]);
            $qrDataUrl = $this->generateQrDataUri($shareUrl);
        }

        return view('certificates.verify', [
            'certificate' => $certificate,
            'shareUrl' => $certificate ? $shareUrl : null,
            'qrUrl' => $certificate ? $qrDataUrl : null,
            'code' => $code,
        ]);
    }

    private function generateQrDataUri(string $shareUrl): ?string
    {
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';

        try {
            $response = Http::timeout(8)->get($apiUrl, [
                'size' => '220x220',
                'data' => $shareUrl,
            ]);

            if ($response->successful() && $response->body()) {
                return 'data:image/png;base64,'.base64_encode($response->body());
            }
        } catch (\Throwable $exception) {
            Log::warning('Unable to generate QR code', [
                'message' => $exception->getMessage(),
            ]);
        }

        return null;
    }
    private function logVerification(Certificate $certificate, Request $request, string $source): void
    {
        CertificateVerificationLog::create([
            'certificate_id' => $certificate->id,
            'source' => $source,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'meta' => [
                'locale' => app()->getLocale(),
            ],
        ]);
    }
}


