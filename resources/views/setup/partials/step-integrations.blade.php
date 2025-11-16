<div class="space-y-10">
    <div>
        <h2 class="text-xl font-semibold">{{ __('Conecta tus integraciones esenciales') }}</h2>
        <p class="text-sm text-slate-400">{{ __('Puedes dejar las credenciales en blanco si prefieres trabajar en modo gratuito y completarlas más tarde desde el panel de administración.') }}</p>
    </div>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Video y streaming') }}</h3>
        <p class="text-xs text-slate-400">{{ __('YouTube funciona como fallback gratuito. Completa las otras credenciales si quieres habilitar proveedores premium.') }}</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-xs uppercase text-slate-400">YouTube origin
                <input type="text" wire:model.defer="integrations.video.YOUTUBE_ORIGIN" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ config('app.url') }}">
            </label>
            <label class="text-xs uppercase text-slate-400">Vimeo token
                <input type="text" wire:model.defer="integrations.video.VIMEO_TOKEN" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-xs uppercase text-slate-400">Cloudflare Stream token
                <input type="text" wire:model.defer="integrations.video.CLOUDFLARE_STREAM_TOKEN" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-xs uppercase text-slate-400">Cloudflare Account ID
                <input type="text" wire:model.defer="integrations.video.CLOUDFLARE_ACCOUNT_ID" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
        </div>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Storage & realtime') }}</h3>
        <p class="text-xs text-slate-400">{{ __('Activa los toggles gratuitos para trabajar en modo local mientras preparas tus credenciales S3/Pusher.') }}</p>
        <div class="grid gap-4 md:grid-cols-3">
            <label class="flex items-center gap-2 text-xs uppercase text-slate-300">
                <input type="checkbox" wire:model.defer="integrations.storage.FORCE_FREE_STORAGE" class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
                {{ __('Forzar almacenamiento local') }}
            </label>
            <label class="flex items-center gap-2 text-xs uppercase text-slate-300">
                <input type="checkbox" wire:model.defer="integrations.storage.FORCE_FREE_REALTIME" class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
                {{ __('Forzar realtime local') }}
            </label>
            <label class="flex items-center gap-2 text-xs uppercase text-slate-300">
                <input type="checkbox" wire:model.defer="integrations.storage.FORCE_YOUTUBE_ONLY" class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
                {{ __('Solo YouTube') }}
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach([
                'AWS_ACCESS_KEY_ID' => 'AWS access key',
                'AWS_SECRET_ACCESS_KEY' => 'AWS secret',
                'AWS_BUCKET' => 'Bucket',
                'AWS_ENDPOINT' => 'Endpoint',
                'AWS_DEFAULT_REGION' => 'Región',
            ] as $field => $label)
                <label class="text-xs uppercase text-slate-400">{{ __($label) }}
                    <input type="text" wire:model.defer="integrations.storage.$field" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            @endforeach
            <label class="flex items-center gap-2 text-xs uppercase text-slate-300">
                <input type="checkbox" wire:model.defer="integrations.storage.AWS_USE_PATH_STYLE_ENDPOINT" class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
                {{ __('Usar path style endpoint') }}
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach([
                'PUSHER_APP_ID' => 'Pusher app id',
                'PUSHER_APP_KEY' => 'Pusher key',
                'PUSHER_APP_SECRET' => 'Pusher secret',
                'PUSHER_APP_CLUSTER' => 'Cluster',
            ] as $field => $label)
                <label class="text-xs uppercase text-slate-400">{{ __($label) }}
                    <input type="text" wire:model.defer="integrations.storage.$field" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Correo y notificaciones') }}</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach([
                'MAIL_MAILER' => 'Mailer',
                'MAIL_HOST' => 'Host',
                'MAIL_PORT' => 'Puerto',
                'MAIL_USERNAME' => 'Usuario',
                'MAIL_PASSWORD' => 'Contraseña',
                'MAIL_ENCRYPTION' => 'Encriptación',
                'MAIL_FROM_ADDRESS' => 'Correo remitente',
                'MAIL_FROM_NAME' => 'Nombre remitente',
            ] as $field => $label)
                <label class="text-xs uppercase text-slate-400">{{ __($label) }}
                    <input type="{{ $field === 'MAIL_PASSWORD' ? 'password' : 'text' }}" wire:model.defer="integrations.mail.$field" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Marketing & SEO') }}</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach([
                'GA4_MEASUREMENT_ID' => 'GA4 Measurement ID',
                'GA4_API_SECRET' => 'GA4 API Secret',
                'RECAPTCHA_SITE_KEY' => 'reCAPTCHA site key',
                'RECAPTCHA_SECRET_KEY' => 'reCAPTCHA secret key',
            ] as $field => $label)
                <label class="text-xs uppercase text-slate-400">{{ __($label) }}
                    <input type="text" wire:model.defer="integrations.marketing.$field" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Automatización & Integraciones') }}</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach([
                'GOOGLE_CLIENT_ID' => 'Google OAuth Client ID',
                'GOOGLE_CLIENT_SECRET' => 'Google OAuth Secret',
                'GOOGLE_REDIRECT_URI' => 'Redirect URI',
                'GOOGLE_SERVICE_ACCOUNT_JSON_PATH' => 'Service account json path',
                'SHEET_ID' => 'Google Sheet ID',
                'WEBHOOKS_MAKE_SECRET' => 'Make secret',
                'MAKE_WEBHOOK_URL' => 'Make webhook URL',
                'DISCORD_WEBHOOK_URL' => 'Discord webhook',
                'DISCORD_WEBHOOK_USERNAME' => 'Discord username',
                'DISCORD_WEBHOOK_AVATAR' => 'Discord avatar URL',
                'DISCORD_WEBHOOK_THREAD_ID' => 'Discord thread ID',
                'WHATSAPP_DEEPLINK' => 'WhatsApp deeplink',
            ] as $field => $label)
                <label class="text-xs uppercase text-slate-400">{{ __($label) }}
                    <input type="text" wire:model.defer="integrations.automation.$field" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            @endforeach
        </div>
        <label class="mt-4 flex items-center gap-2 text-xs uppercase text-slate-300">
            <input type="checkbox" wire:model.defer="integrations.automation.GOOGLE_SHEETS_ENABLED" class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
            {{ __('Habilitar Google Sheets') }}
        </label>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Observabilidad') }}</h3>
        <label class="mt-4 block text-xs uppercase text-slate-400">Sentry DSN
            <input type="text" wire:model.defer="integrations.observability.SENTRY_LARAVEL_DSN" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
        </label>
    </section>
</div>
