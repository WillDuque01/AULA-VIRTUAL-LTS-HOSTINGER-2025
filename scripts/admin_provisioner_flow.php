<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Provisioning\CredentialProvisioner;
use App\Support\Provisioning\Dto\ProvisioningMeta;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    echo '['.now()->toDateTimeString()."] Admin QA no disponible para provisioner.\n";

    return;
}

Auth::login($admin);

$provisioner = app(CredentialProvisioner::class);

$payload = [
    'MAIL_MAILER' => 'log',
    'MAIL_HOST' => 'smtp.mailtrap.io',
    'MAIL_PORT' => '2525',
    'MAIL_USERNAME' => 'qa_mailtrap_user',
    'MAIL_PASSWORD' => 'qa_mailtrap_secret',
    'MAIL_ENCRYPTION' => 'tls',
    'MAIL_FROM_ADDRESS' => 'noreply.qa@letstalkspanish.io',
    'MAIL_FROM_NAME' => 'LTS_QA_Notifier',

    'PUSHER_APP_ID' => '120001',
    'PUSHER_APP_KEY' => 'pusher-qa-key',
    'PUSHER_APP_SECRET' => 'pusher-qa-secret',
    'PUSHER_APP_CLUSTER' => 'mt1',

    'AWS_ACCESS_KEY_ID' => 'QAACCESSKEY',
    'AWS_SECRET_ACCESS_KEY' => 'QASECRETACCESS',
    'AWS_BUCKET' => 'lts-qa-bucket',
    'AWS_DEFAULT_REGION' => 'us-east-1',
    'AWS_ENDPOINT' => 'https://s3.us-east-1.amazonaws.com',
    'AWS_USE_PATH_STYLE_ENDPOINT' => 'false',

    'WHATSAPP_ENABLED' => 'true',
    'WHATSAPP_TOKEN' => 'qa-whatsapp-token',
    'WHATSAPP_PHONE_ID' => '1234567890',
    'WHATSAPP_DEFAULT_TO' => '+573001112233',
    'WHATSAPP_DEEPLINK' => 'https://wa.me/573001112233?text=Hola%20QA',

    'DISCORD_WEBHOOK_URL' => 'https://discord.com/api/webhooks/qa/webhook',
    'DISCORD_WEBHOOK_USERNAME' => 'QA_Announcer',
    'DISCORD_WEBHOOK_AVATAR' => 'https://cdn.letstalkspanish.io/qa/avatar.png',

    'GOOGLE_CLIENT_ID' => 'qa-google-client.apps.googleusercontent.com',
    'GOOGLE_CLIENT_SECRET' => 'qa-google-secret',
    'GOOGLE_REDIRECT_URI' => 'https://app.letstalkspanish.io/oauth/google/callback',
] ;

$meta = ProvisioningMeta::make(
    user: $admin,
    ipAddress: '127.0.0.1',
    userAgent: 'qa-admin-provisioner',
    shouldWriteEnv: true,
    shouldCacheConfig: true,
    shouldPersistAudit: true,
);

$provisioner->apply($payload, $meta);

echo '['.now()->toDateTimeString()."] Provisioner actualizado por admin_provisioner_flow.\n";

