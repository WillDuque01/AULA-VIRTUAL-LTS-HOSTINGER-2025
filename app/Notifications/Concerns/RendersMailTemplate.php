<?php

namespace App\Notifications\Concerns;

use App\Support\Branding\Branding;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;

trait RendersMailTemplate
{
    protected function renderMail(string $view, array $data, string $subjectKey, array $subjectParams = [], ?string $titleKey = null): MailMessage
    {
        $notifiable = Arr::get($data, 'notifiable');
        $locale = $this->resolveLocale($notifiable);
        $originalLocale = app()->getLocale();

        if ($locale !== $originalLocale) {
            app()->setLocale($locale);
        }

        $branding = Branding::info();
        $payload = array_merge([
            'brandName' => $branding['name'],
            'brand' => $branding,
            'supportEmail' => $branding['support_email'] ?? config('mail.from.address'),
            'subjectKey' => $subjectKey,
            'subjectParams' => $subjectParams,
            'titleKey' => $titleKey,
            'signatureKey' => 'email.signature',
            'greetingKey' => 'email.greeting',
            'recipientName' => $this->resolveRecipientName($data, $notifiable),
        ], $data);

        $message = (new MailMessage())
            ->view($view, $payload)
            ->subject(__($subjectKey, $subjectParams, $locale));

        if ($locale !== $originalLocale) {
            app()->setLocale($originalLocale);
        }

        return $message;
    }

    protected function resolveLocale($notifiable): string
    {
        return method_exists($notifiable, 'preferredLocale')
            ? $notifiable->preferredLocale()
            : ($notifiable->preferred_locale ?? app()->getLocale());
    }

    protected function resolveRecipientName(array $data, $notifiable): string
    {
        if (! empty($data['recipientName'])) {
            return $data['recipientName'];
        }

        if ($notifiable && property_exists($notifiable, 'name')) {
            return (string) $notifiable->name;
        }

        return __('Student');
    }
}
