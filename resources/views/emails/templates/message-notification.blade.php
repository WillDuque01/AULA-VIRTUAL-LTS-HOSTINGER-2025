@extends('emails.layouts.base')

@section('subject', __($subjectKey ?? 'email.subjects.teacher_message', $subjectParams ?? []))
@section('title', __($titleKey ?? 'email.subjects.teacher_message', $subjectParams ?? []))

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6;">{!! __($greetingKey ?? 'email.greeting', ['name' => $recipientName]) !!}</p>

    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: {{ $emailPalette['text'] }};">{!! $intro ?? '' !!}</p>

    @component('emails.components.panel')
        <h3 style="margin: 0 0 12px 0; color: {{ $emailPalette['accent'] }}; font-size: 16px;">{{ __('email.blocks.message.preview') }}</h3>
        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['text'] }};">{!! nl2br(e($messagePreview)) !!}</p>
    @endcomponent

    @component('emails.components.button', ['url' => $messageUrl])
        {{ __($ctaLabel ?? 'email.actions.view_message') }}
    @endcomponent

    @if(!empty($additionalContent))
        <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['muted'] }};">{!! $additionalContent !!}</p>
    @endif

    <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 1.6; color: {{ $emailPalette['text'] }};">{{ __('email.blocks.footer.support', ['email' => $supportEmail]) }}</p>
    <p style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.6; color: {{ $emailPalette['muted'] }};">{{ __('email.blocks.footer.preferences') }}</p>

    <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;">{{ __($signatureKey ?? 'email.signature', ['brand' => $brandName]) }}</p>
@endsection
