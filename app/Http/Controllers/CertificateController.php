<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateVerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($certificate) {
            $certificate->increment('verified_count');
            $certificate->forceFill(['last_verified_at' => now()])->save();
            $this->logVerification($certificate, $request, 'web');

            $shareUrl = route('certificates.verify', ['code' => $certificate->code]);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode($shareUrl);
        }

        return view('certificates.verify', [
            'certificate' => $certificate,
            'shareUrl' => $certificate ? $shareUrl : null,
            'qrUrl' => $certificate ? $qrUrl : null,
            'code' => $code,
        ]);
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


