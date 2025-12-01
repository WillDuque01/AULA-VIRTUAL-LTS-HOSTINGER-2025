<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
            @endphp
            @if($user && $user->hasRole('Admin'))
                <livewire:admin.dashboard lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Se carga bajo demanda para reducir el TTFB --}}
            @elseif($user && $user->hasAnyRole(['teacher_admin', 'Profesor']))
                <livewire:professor.dashboard lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Dashboard docente pesado se carga en diferido --}}
                <div class="mt-10 space-y-10">
                    <livewire:professor.discord-practice-planner lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Planner se inicializa al entrar en viewport --}}
                    <livewire:professor.practice-packages-manager lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Módulo de paquetes también usa lazy --}}
                </div>
            @elseif($user && $user->hasRole('teacher'))
                <livewire:teacher.dashboard lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Dashboard teacher con carga diferida --}}
            @else
                <livewire:student.dashboard lazy /> {{-- [AGENTE: GPT-5.1 CODEX] - Student dashboard se renderiza cuando es necesario --}}
            @endif
        </div>
    </div>
</x-app-layout>
