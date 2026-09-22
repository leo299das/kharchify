<section class="animate__animated animate__fadeInUp bg-white p-8 rounded-2xl shadow-sm border border-red-50" style="animation-delay: 0.2s;">
    <header>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-red-50 rounded-lg">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">
                {{ __('Delete Account') }}
            </h2>
        </div>

        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="mt-6">
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="hover:scale-105 active:scale-95 transition-transform duration-150 shadow-lg shadow-red-100 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700"
        >
            {{ __('Delete Account') }}
        </x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 bg-white rounded-2xl overflow-hidden">
            @csrf
            @method('delete')

            <div class="flex items-center gap-4 text-red-600 mb-4 animate__animated animate__pulse animate__infinite">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-2xl font-bold">
                    {{ __('Final Warning') }}
                </h2>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed">
                {{ __('This action is permanent and cannot be undone. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6 group">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl transition-all shadow-sm"
                    placeholder="{{ __('Verify your password...') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <x-secondary-button 
                    x-on:click="$dispatch('close')" 
                    class="rounded-xl border-gray-200 hover:bg-gray-50 transition-colors py-3"
                >
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3 rounded-xl bg-red-600 hover:bg-red-700 shadow-lg shadow-red-200 px-6 py-3 animate__animated animate__shakeX animate__delay-1s">
                    {{ __('Permanently Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>