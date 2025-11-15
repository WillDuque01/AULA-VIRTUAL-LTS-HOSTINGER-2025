@extends('emails.layouts.base')

@section('subject', __($subjectKey ?? 'email.subjects.module_unlocked', $subjectParams ?? []))
@section('title', __($titleKey ?? 'email.subjects.module_unlocked', $subjectParams ?? []))

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6;">{!! __($greetingKey ?? 'email.greeting', ['name' => $recipientName]) !!}</p>

    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6;">{!! $intro ?? '' !!}</p>

    @component('emails.components.panel')
        <h3 style="margin: 0 0 12px 0; color: #38bdf8; font-size: 16px;">{{ $moduleTitle }}</h3>
        <p style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.6; color: #cbd5f5;">{{ $courseTitle }}</p>
        <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #94a3b8;">{{ __('email.blocks.course.available_for', ['audience' => $audienceLabel]) }}</p>
    @endcomponent

    @component('emails.components.button', ['url' => $moduleUrl])
        {{ __($ctaLabel ?? 'email.blocks.course.start_learning') }}
    @endcomponent

    @if(!empty($additionalContent))
        <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6; color: #cbd5f5;">{!! $additionalContent !!}</p>
    @endif

    <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 1.6;">{{ __('email.blocks.footer.support', ['email' => $supportEmail]) }}</p>
    <p style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.6; color: #94a3b8;">{{ __('email.blocks.footer.preferences') }}</p>

    <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;">{{ __($signatureKey ?? 'email.signature', ['brand' => $brandName]) }}</p>
@endsection
