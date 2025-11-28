@extends('layouts.guest')

@section('title', __('Setup Wizard'))
@section('guest_outer_class', 'min-h-screen bg-slate-900 text-slate-100')
@section('guest_card_class', 'w-full px-0 py-0 bg-transparent shadow-none sm:rounded-none')
@section('guest_show_logo', 'false')

@section('content')
    <livewire:setup.setup-wizard />
@endsection
