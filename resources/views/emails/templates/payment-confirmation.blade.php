@extends('emails.layouts.base')

@section('subject', __($subjectKey ?? 'email.subjects.payment_confirmed', $subjectParams ?? []))
@section('title', __($titleKey ?? 'email.subjects.payment_confirmed', $subjectParams ?? []))

@section('content')
    <p style="margin: 0 0 18px 0; font-size: 16px; line-height: 1.6;">
        {!! __($greetingKey ?? 'email.greeting', ['name' => $recipientName]) !!}
    </p>

    <p style="margin: 0 0 18px 0; font-size: 15px; line-height: 1.6; color: {{ $emailPalette['text'] }};">
        {!! $intro ?? __('email.body.payment_confirmed.intro', ['tier' => $tierName ?? __('email.blocks.payment.plan_default')]) !!}
    </p>

    @component('emails.components.panel')
        <p style="margin: 0 0 6px 0; font-size: 15px; color: {{ $emailPalette['accent'] }};">
            {{ __('email.blocks.payment.tier', ['tier' => $tierName ?? __('email.blocks.payment.plan_default')]) }}
        </p>
        @if(!empty($amount) && !empty($currency))
            <p style="margin: 0 0 6px 0; font-size: 14px; color: {{ $emailPalette['text'] }};">
                {{ __('email.blocks.payment.amount', ['amount' => $amount, 'currency' => $currency]) }}
            </p>
        @endif
        <p style="margin: 0 0 6px 0; font-size: 14px; color: {{ $emailPalette['muted'] }};">
            {{ __('email.blocks.payment.provider', ['provider' => $provider ?? '']) }}
        </p>
        @if(!empty($statusLabel))
            <p style="margin: 0; font-size: 13px; color: {{ $emailPalette['text'] }};">
                {{ __('email.blocks.payment.status', ['status' => $statusLabel]) }}
            </p>
        @endif
    @endcomponent

    @if(!empty($dashboardUrl))
        @component('emails.components.button', ['url' => $dashboardUrl])
            {{ __($ctaLabel ?? 'email.actions.view_courses') }}
        @endcomponent
    @endif

    <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['text'] }};">
        {{ __('email.blocks.payment.help', ['email' => $supportEmail]) }}
    </p>

    <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;">
        {{ __($signatureKey ?? 'email.signature', ['brand' => $brandName ?? config('app.name')]) }}
    </p>
@endsection
