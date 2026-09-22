<section class="animate__animated animate__fadeInUp bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
    <header>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 rounded-lg">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">
                {{ __('Profile Information') }}
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
            {{ __("Update your account's profile information and email address to keep your identity secure.") }}
        </p>
    </header>

    <!-- Form for verification -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('patch')

        <!-- Name Input -->
        <div class="group">
            <x-input-label for="name" :value="__('Name')" class="group-focus-within:text-indigo-600 transition-colors" />
            <div class="relative mt-1">
                <x-text-input id="name" name="name" type="text" 
                    class="block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl transition-all duration-200 shadow-sm" 
                    :value="old('name', $user->name)" required autofocus autocomplete="name" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email Input -->
        <div class="group">
            <x-input-label for="email" :value="__('Email')" class="group-focus-within:text-indigo-600 transition-colors" />
            <div class="relative mt-1">
                <x-text-input id="email" name="email" type="email" 
                    class="block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl transition-all duration-200 shadow-sm" 
                    :value="old('email', $user->email)" required autocomplete="username" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100 animate__animated animate__headShake">
                    <p class="text-sm text-amber-800 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        {{ __('Your email address is unverified.') }}
                    </p>

                    <button form="send-verification" class="mt-2 text-xs font-bold uppercase tracking-wider text-amber-700 hover:text-amber-900 transition-colors underline-offset-4 hover:underline">
                        {{ __('Re-send Verification Email') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-semibold text-xs text-green-600">
                            {{ __('✓ A fresh link has been sent to your inbox.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 transition-transform duration-150 py-3 px-8 rounded-xl shadow-lg shadow-indigo-200">
                {{ __('Save Changes') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm font-medium text-green-600 flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Profile Updated.') }}
                </p>
            @endif
        </div>
    </form>
</section>

<!-- Add this to your layout head if not already there -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>