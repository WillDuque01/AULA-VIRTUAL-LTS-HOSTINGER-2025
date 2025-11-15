<?php

namespace App\Support\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateGenerator
{
    public function generate(User $user, Course $course, array $context = []): Certificate
    {
        $code = strtoupper(Str::random(10));
        $issuedAt = now();

        $data = array_merge([
            'user' => $user,
            'course' => $course,
            'issued_at' => $issuedAt,
            'code' => $code,
        ], $context);

        $pdf = Pdf::loadView('certificates.pdf', $data);
        $directory = 'certificates';
        $filename = sprintf('certificate-%s-%s.pdf', $course->id, Str::slug($user->name).'-'.$code);
        $path = $directory.'/'.$filename;

        Storage::disk('local')->put($path, $pdf->output());

        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'code' => $code,
            'file_path' => $path,
            'issued_at' => $issuedAt,
            'metadata' => [
                'percent' => $context['percent'] ?? null,
            ],
        ]);
    }
}


