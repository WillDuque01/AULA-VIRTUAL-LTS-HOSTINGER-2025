<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" :value="__('Phone / WhatsApp')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
            <div>
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" autocomplete="country-name" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="state" :value="__('State / Region')" />
                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" autocomplete="address-level1" />
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>
        </div>

        @php
            $canEditTeacherFields = $user->hasAnyRole(['teacher','teacher_admin','Profesor']);
        @endphp
        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-800">Teacher profile</p>
                @unless($canEditTeacherFields)
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Disponible para Teacher Admin') }}</span>
                @endunless
            </div>

            @if($canEditTeacherFields)
                <div>
                    <x-input-label for="headline" :value="__('Headline')" />
                    <x-text-input id="headline" name="headline" type="text" class="mt-1 block w-full" :value="old('headline', $user->headline)" />
                    <x-input-error class="mt-2" :messages="$errors->get('headline')" />
                </div>
                <div>
                    <x-input-label for="bio" :value="__('Bio')" />
                    <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('bio', $user->bio) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="teaching_since" :value="__('Teaching since')" />
                        <x-text-input id="teaching_since" name="teaching_since" type="text" class="mt-1 block w-full" :value="old('teaching_since', $user->teaching_since)" />
                        <x-input-error class="mt-2" :messages="$errors->get('teaching_since')" />
                    </div>
                    <div>
                        <x-input-label for="linkedin_url" :value="__('LinkedIn / Portfolio URL')" />
                        <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $user->linkedin_url)" />
                        <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="specialties" :value="__('Specialties (comma separated)')" />
                        <x-text-input id="specialties" name="specialties" type="text" class="mt-1 block w-full" :value="old('specialties', implode(', ', $user->specialties ?? []))" />
                    </div>
                    <div>
                        <x-input-label for="languages" :value="__('Languages (comma separated)')" />
                        <x-text-input id="languages" name="languages" type="text" class="mt-1 block w-full" :value="old('languages', implode(', ', $user->languages ?? []))" />
                    </div>
                    <div>
                        <x-input-label for="certifications" :value="__('Certifications (comma separated)')" />
                        <x-text-input id="certifications" name="certifications" type="text" class="mt-1 block w-full" :value="old('certifications', implode(', ', $user->certifications ?? []))" />
                    </div>
                </div>
                <div>
                    <x-input-label for="teacher_notes" :value="__('Teacher notes')" />
                    <textarea id="teacher_notes" name="teacher_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('teacher_notes', $user->teacher_notes) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('teacher_notes')" />
                </div>
            @else
                <p class="text-xs text-slate-500">
                    {{ __('Esta sección se activa automáticamente cuando el usuario tiene rol Teacher Admin o Profesor. Solicita acceso al equipo académico si necesitas completar tu bio docente.') }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
