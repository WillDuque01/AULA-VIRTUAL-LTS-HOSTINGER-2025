@extends('emails.layouts.base')

@section('subject', __($subjectKey, $subjectParams ?? []))
@section('title', __($titleKey ?? $subjectKey, $subjectParams ?? []))

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6;">{!! __($greetingKey ?? 'email.greeting', ['name' => $recipientName]) !!}</p>

    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: {{ $emailPalette['text'] }};">{!! $intro ?? '' !!}</p>

    @component('emails.components.panel')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; color: {{ $emailPalette['text'] }};">
            <tr>
                <td style="padding: 4px 0; font-weight: 600;">{{ __('email.blocks.subscription.status', ['status' => $statusLabel]) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">{{ __('email.blocks.subscription.tier', ['tier' => $tierName]) }}</td>
            </tr>
            @if($renewsAt)
                <tr>
                    <td style="padding: 4px 0;">{{ __('email.blocks.subscription.renews_at', ['date' => $renewsAt]) }}</td>
                </tr>
            @endif
            @if($expiresAt)
                <tr>
                    <td style="padding: 4px 0;">{{ __('email.blocks.subscription.expires_at', ['date' => $expiresAt]) }}</td>
                </tr>
            @endif
        </table>
    @endcomponent

    @if(!empty($ctaUrl))
        @component('emails.components.button', ['url' => $ctaUrl])
            {{ __($ctaLabel ?? 'email.actions.manage_subscription') }}
        @endcomponent
    @endif

    @if(!empty($additionalContent))
        <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['muted'] }};">{!! $additionalContent !!}</p>
    @endif

    <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['text'] }};">{{ __('email.blocks.footer.support', ['email' => $supportEmail]) }}</p>
    <p style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.6; color: {{ $emailPalette['muted'] }};">{{ __('email.blocks.footer.preferences') }}</p>

    <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;">{{ __($signatureKey ?? 'email.signature', ['brand' => $brandName]) }}</p>
@endsection
