<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ProvisionerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-settings');
    }

    public function rules(): array
    {
        return [
            'GOOGLE_CLIENT_ID' => ['nullable', 'string', 'max:255'],
            'GOOGLE_CLIENT_SECRET' => ['nullable', 'string', 'max:255'],
            'GOOGLE_REDIRECT_URI' => ['nullable', 'url', 'max:255'],
            'GOOGLE_SERVICE_ACCOUNT_JSON_PATH' => ['nullable', 'string', 'max:255'],
            'SHEET_ID' => ['nullable', 'string', 'max:255'],

            'PUSHER_APP_ID' => ['nullable', 'string', 'max:255'],
            'PUSHER_APP_KEY' => ['nullable', 'string', 'max:255'],
            'PUSHER_APP_SECRET' => ['nullable', 'string', 'max:255'],
            'PUSHER_APP_CLUSTER' => ['nullable', 'string', 'max:255'],

            'AWS_ACCESS_KEY_ID' => ['nullable', 'string', 'max:255'],
            'AWS_SECRET_ACCESS_KEY' => ['nullable', 'string', 'max:255'],
            'AWS_BUCKET' => ['nullable', 'string', 'max:255'],
            'AWS_ENDPOINT' => ['nullable', 'url', 'max:255'],
            'AWS_DEFAULT_REGION' => ['nullable', 'string', 'max:255'],
            'AWS_USE_PATH_STYLE_ENDPOINT' => ['nullable', 'boolean'],

            'VIMEO_TOKEN' => ['nullable', 'string', 'max:255'],
            'CLOUDFLARE_STREAM_TOKEN' => ['nullable', 'string', 'max:255'],
            'CLOUDFLARE_ACCOUNT_ID' => ['nullable', 'string', 'max:255'],
            'YOUTUBE_ORIGIN' => ['nullable', 'url', 'max:255'],

            'MAIL_MAILER' => ['nullable', 'string', 'max:255'],
            'MAIL_HOST' => ['nullable', 'string', 'max:255'],
            'MAIL_PORT' => ['nullable', 'numeric', 'between:1,65535'],
            'MAIL_USERNAME' => ['nullable', 'string', 'max:255'],
            'MAIL_PASSWORD' => ['nullable', 'string', 'max:255'],
            'MAIL_ENCRYPTION' => ['nullable', 'string', 'max:255'],
            'MAIL_FROM_ADDRESS' => ['nullable', 'email', 'max:255'],
            'MAIL_FROM_NAME' => ['nullable', 'string', 'max:255'],

            'WEBHOOKS_MAKE_SECRET' => ['nullable', 'string', 'max:255'],
            'MAKE_WEBHOOK_URL' => ['nullable', 'url', 'max:255'],
            'DISCORD_WEBHOOK_URL' => ['nullable', 'url', 'max:255'],

            'GOOGLE_SHEETS_ENABLED' => ['nullable', 'boolean'],
            'FORCE_FREE_STORAGE' => ['nullable', 'boolean'],
            'FORCE_FREE_REALTIME' => ['nullable', 'boolean'],
            'FORCE_YOUTUBE_ONLY' => ['nullable', 'boolean'],
        ];
    }
}