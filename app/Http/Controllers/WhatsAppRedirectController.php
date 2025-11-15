<?php

namespace App\Http\Controllers;

use App\Support\Integrations\WhatsAppCtaLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WhatsAppRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $target = Crypt::decryptString($request->query('target'));

        $meta = [];
        if ($encryptedMeta = $request->query('meta')) {
            try {
                $meta = json_decode(Crypt::decryptString($encryptedMeta), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                $meta = [];
            }
        }

        $context = $request->query('context', 'unknown');
        WhatsAppCtaLogger::record($context, $meta ?? []);

        return redirect()->away($target);
    }
}


