<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php($user = auth()->user())
            @if($user && $user->hasRole('Admin'))
                <livewire:admin.dashboard />
            @elseif($user && $user->hasAnyRole(['teacher_admin', 'Profesor']))
                <livewire:professor.dashboard />
                <div class="mt-10 space-y-10">
                    <livewire:professor.discord-practice-planner />
                    <livewire:professor.practice-packages-manager />
                </div>
            @elseif($user && $user->hasRole('teacher'))
                <livewire:teacher.dashboard />
            @else
                <livewire:student.dashboard />
            @endif
        </div>
    </div>
</x-app-layout>
