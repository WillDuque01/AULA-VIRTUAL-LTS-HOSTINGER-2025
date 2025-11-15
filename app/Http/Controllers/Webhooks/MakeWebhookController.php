<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\MakeWebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MakeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.make.secret');

        if ($secret) {
            $signature = $request->header('X-LMS-Signature', '');
            $expected = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));

            if (! hash_equals($expected, $signature)) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        MakeWebhookLog::create([
            'event' => $request->header('X-LMS-Event'),
            'signature' => $request->header('X-LMS-Signature'),
            'payload' => $request->all(),
        ]);

        return new JsonResponse(['status' => 'accepted'], 202);
    }
}

