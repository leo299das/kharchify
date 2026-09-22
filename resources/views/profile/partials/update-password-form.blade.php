<section class="animate__animated animate__fadeInUp bg-white p-8 rounded-2xl shadow-sm border border-gray-100" style="animation-delay: 0.1s;">
    <header>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-50 rounded-lg">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">
                {{ __('Update Password') }}
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="group animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="group-focus-within:text-amber-600 transition-colors" />
            <div class="relative mt-1">
                <x-text-input id="update_password_current_password" name="current_password" type="password" 
                    class="block w-full border-gray-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl transition-all duration-200 shadow-sm" 
                    autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <!-- New Password -->
        <div class="group animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <x-input-label for="update_password_password" :value="__('New Password')" class="group-focus-within:text-amber-600 transition-colors" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password" name="password" type="password" 
                    class="block w-full border-gray-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl transition-all duration-200 shadow-sm" 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="group animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="group-focus-within:text-amber-600 transition-colors" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                    class="block w-full border-gray-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl transition-all duration-200 shadow-sm" 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-4 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <x-primary-button class="bg-gray-900 hover:bg-black active:scale-95 transition-transform duration-150 py-3 px-8 rounded-xl shadow-lg shadow-gray-200">
                {{ __('Update Password') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
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
                    class="text-sm font-medium text-emerald-600 flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    {{ __('Password Secured.') }}
                </p>
            @endif
        </div>
    </form>
</section>