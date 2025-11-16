<?php

namespace App\Http\Livewire\Setup;

use App\Models\SetupState;
use App\Models\User;
use App\Support\Guides\GuideRegistry;
use App\Support\Provisioning\CredentialProvisioner;
use App\Support\Provisioning\Dto\ProvisioningMeta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Throwable;

class SetupWizard extends Component
{
    public int $step = 1;

    public array $admin = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public array $integrations = [];

    public array $payments = [];

    public bool $adminCreated = false;

    protected CredentialProvisioner $provisioner;

    public function boot(CredentialProvisioner $provisioner): void
    {
        $this->provisioner = $provisioner;
    }

    public function mount(): void
    {
        if (SetupState::isCompleted()) {
            if (Auth::check()) {
                Redirect::to(route('dashboard'))->send();
            }

            Redirect::to('/login')->send();
        }

        $this->integrations = $this->defaultIntegrations();
        $this->payments = $this->defaultPayments();

        if (User::query()->exists()) {
            $this->adminCreated = true;
            $this->step = 2;
        }
    }

    public function render()
    {
        return view('livewire.setup.setup-wizard', [
            'steps' => $this->steps(),
            'integrationGuides' => GuideRegistry::integrationGuides(),
            'wizardGuide' => GuideRegistry::context('setup.integrations'),
        ]);
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->handleAdminStep();
        } elseif ($this->step === 2) {
            $this->validateIntegrations();
        }

        $this->step = min($this->step + 1, count($this->steps()));
    }

    public function previous(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function finish()
    {
        $this->validatePayments();

        $credentials = array_filter($this->compiledCredentials(), fn ($value) => $value !== null && $value !== '');

        $this->provisioner->apply($credentials, ProvisioningMeta::make(user: Auth::user()));

        SetupState::markCompleted([
            'admin_email' => Auth::user()?->email,
            'steps' => $this->steps(),
        ]);

        session()->flash('setup.completed', true);

        return redirect()->route('dashboard');
    }

    private function handleAdminStep(): void
    {
        if ($this->adminCreated) {
            return;
        }

        $this->validate([
            'admin.name' => ['required', 'string', 'max:255'],
            'admin.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin.password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $this->admin['name'],
            'email' => $this->admin['email'],
            'password' => Hash::make($this->admin['password']),
        ]);

        if (method_exists($user, 'assignRole') && class_exists(Role::class)) {
            if (Role::query()->where('name', 'admin')->exists()) {
                try {
                    $user->assignRole('admin');
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        Auth::login($user);
        $this->adminCreated = true;
        $this->admin['password'] = '';
        $this->admin['password_confirmation'] = '';
    }

    private function validateIntegrations(): void
    {
        // Placeholder for detailed validation per category.
    }

    private function validatePayments(): void
    {
        // Placeholder for payment validation (e.g., PayPal mode values).
    }

    private function compiledCredentials(): array
    {
        $credentials = [];

        $video = $this->integrations['video'] ?? [];
        $credentials['YOUTUBE_ORIGIN'] = $video['YOUTUBE_ORIGIN'] ?? '';
        $credentials['VIMEO_TOKEN'] = $video['VIMEO_TOKEN'] ?? '';
        $credentials['CLOUDFLARE_STREAM_TOKEN'] = $video['CLOUDFLARE_STREAM_TOKEN'] ?? '';
        $credentials['CLOUDFLARE_ACCOUNT_ID'] = $video['CLOUDFLARE_ACCOUNT_ID'] ?? '';

        $storage = $this->integrations['storage'] ?? [];
        $credentials['FORCE_FREE_STORAGE'] = ($storage['FORCE_FREE_STORAGE'] ?? true) ? 'true' : 'false';
        $credentials['AWS_ACCESS_KEY_ID'] = $storage['AWS_ACCESS_KEY_ID'] ?? '';
        $credentials['AWS_SECRET_ACCESS_KEY'] = $storage['AWS_SECRET_ACCESS_KEY'] ?? '';
        $credentials['AWS_BUCKET'] = $storage['AWS_BUCKET'] ?? '';
        $credentials['AWS_ENDPOINT'] = $storage['AWS_ENDPOINT'] ?? '';
        $credentials['AWS_DEFAULT_REGION'] = $storage['AWS_DEFAULT_REGION'] ?? '';
        $credentials['AWS_USE_PATH_STYLE_ENDPOINT'] = ($storage['AWS_USE_PATH_STYLE_ENDPOINT'] ?? true) ? 'true' : 'false';

        $credentials['FORCE_FREE_REALTIME'] = ($storage['FORCE_FREE_REALTIME'] ?? true) ? 'true' : 'false';
        $credentials['PUSHER_APP_ID'] = $storage['PUSHER_APP_ID'] ?? '';
        $credentials['PUSHER_APP_KEY'] = $storage['PUSHER_APP_KEY'] ?? '';
        $credentials['PUSHER_APP_SECRET'] = $storage['PUSHER_APP_SECRET'] ?? '';
        $credentials['PUSHER_APP_CLUSTER'] = $storage['PUSHER_APP_CLUSTER'] ?? '';
        $credentials['FORCE_YOUTUBE_ONLY'] = ($storage['FORCE_YOUTUBE_ONLY'] ?? false) ? 'true' : 'false';

        $mail = $this->integrations['mail'] ?? [];
        foreach ($mail as $key => $value) {
            $credentials[$key] = $value;
        }

        $marketing = $this->integrations['marketing'] ?? [];
        foreach ($marketing as $key => $value) {
            $credentials[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }

        $automation = $this->integrations['automation'] ?? [];
        $credentials['GOOGLE_SHEETS_ENABLED'] = ($automation['GOOGLE_SHEETS_ENABLED'] ?? false) ? 'true' : 'false';
        unset($automation['GOOGLE_SHEETS_ENABLED']);

        foreach ($automation as $key => $value) {
            $credentials[$key] = $value;
        }

        $observability = $this->integrations['observability'] ?? [];
        foreach ($observability as $key => $value) {
            $credentials[$key] = $value;
        }

        $paypal = $this->payments['paypal'] ?? [];
        $credentials['PAYPAL_CLIENT_ID'] = $paypal['client_id'] ?? '';
        $credentials['PAYPAL_CLIENT_SECRET'] = $paypal['client_secret'] ?? '';
        $credentials['PAYPAL_MODE'] = $paypal['mode'] ?? 'sandbox';

        $stripe = $this->payments['stripe'] ?? [];
        $credentials['STRIPE_KEY'] = $stripe['publishable_key'] ?? '';
        $credentials['STRIPE_SECRET'] = $stripe['secret_key'] ?? '';
        $credentials['STRIPE_WEBHOOK_SECRET'] = $stripe['webhook_secret'] ?? '';

        $custom = $this->payments['custom'] ?? [];
        foreach ($custom as $key => $value) {
            if (Str::startsWith($key, 'CUSTOM_PAYMENT_')) {
                $credentials[$key] = $value;
            }
        }

        return $credentials;
    }

    private function steps(): array
    {
        return [
            1 => __('Create administrator'),
            2 => __('Configure integrations'),
            3 => __('Payment gateways'),
        ];
    }

    private function defaultIntegrations(): array
    {
        return [
            'video' => [
                'YOUTUBE_ORIGIN' => '',
                'VIMEO_TOKEN' => '',
                'CLOUDFLARE_STREAM_TOKEN' => '',
                'CLOUDFLARE_ACCOUNT_ID' => '',
            ],
            'storage' => [
                'FORCE_FREE_STORAGE' => true,
                'FORCE_FREE_REALTIME' => true,
                'FORCE_YOUTUBE_ONLY' => false,
                'AWS_ACCESS_KEY_ID' => '',
                'AWS_SECRET_ACCESS_KEY' => '',
                'AWS_BUCKET' => '',
                'AWS_ENDPOINT' => '',
                'AWS_DEFAULT_REGION' => '',
                'AWS_USE_PATH_STYLE_ENDPOINT' => true,
                'PUSHER_APP_ID' => '',
                'PUSHER_APP_KEY' => '',
                'PUSHER_APP_SECRET' => '',
                'PUSHER_APP_CLUSTER' => '',
            ],
            'mail' => [
                'MAIL_MAILER' => 'log',
                'MAIL_HOST' => '',
                'MAIL_PORT' => '',
                'MAIL_USERNAME' => '',
                'MAIL_PASSWORD' => '',
                'MAIL_ENCRYPTION' => '',
                'MAIL_FROM_ADDRESS' => '',
                'MAIL_FROM_NAME' => '',
            ],
            'marketing' => [
                'GA4_ENABLED' => false,
                'GA4_MEASUREMENT_ID' => '',
                'GA4_API_SECRET' => '',
                'MIXPANEL_ENABLED' => false,
                'MIXPANEL_PROJECT_TOKEN' => '',
                'MIXPANEL_API_SECRET' => '',
                'RECAPTCHA_SITE_KEY' => '',
                'RECAPTCHA_SECRET_KEY' => '',
            ],
            'automation' => [
                'GOOGLE_CLIENT_ID' => '',
                'GOOGLE_CLIENT_SECRET' => '',
                'GOOGLE_REDIRECT_URI' => '',
                'GOOGLE_SERVICE_ACCOUNT_JSON_PATH' => 'storage/app/keys/google.json',
                'GOOGLE_SHEETS_ENABLED' => false,
                'SHEET_ID' => '',
                'WEBHOOKS_MAKE_SECRET' => '',
                'MAKE_WEBHOOK_URL' => '',
                'DISCORD_WEBHOOK_URL' => '',
                'DISCORD_WEBHOOK_USERNAME' => '',
                'DISCORD_WEBHOOK_AVATAR' => '',
                'DISCORD_WEBHOOK_THREAD_ID' => '',
                'DISCORD_PRACTICES_REQUEST_THRESHOLD' => 5,
                'DISCORD_PRACTICES_REQUEST_COOLDOWN_MINUTES' => 60,
                'WHATSAPP_DEEPLINK' => '',
            ],
            'observability' => [
                'SENTRY_LARAVEL_DSN' => '',
            ],
        ];
    }

    private function defaultPayments(): array
    {
        return [
            'paypal' => [
                'client_id' => '',
                'client_secret' => '',
                'mode' => 'sandbox',
            ],
            'stripe' => [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
            ],
            'custom' => [
                'CUSTOM_PAYMENT_LABEL' => '',
                'CUSTOM_PAYMENT_URL' => '',
            ],
        ];
    }
}
