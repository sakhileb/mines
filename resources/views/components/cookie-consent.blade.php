@php
    $privacyUrl = route('policy.show');
    $termsUrl = route('terms.show');
@endphp

<div
    x-data="{
        show: false,
        init() {
            if (!localStorage.getItem('cookie_consent')) {
                this.show = true;
            }
        },
        accept() {
            localStorage.setItem('cookie_consent', 'accepted');
            this.show = false;
        },
        decline() {
            localStorage.setItem('cookie_consent', 'declined');
            this.show = false;
        }
    }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6"
    role="dialog"
    aria-label="Cookie consent"
    aria-live="polite"
>
    <div class="max-w-4xl mx-auto bg-gray-800 border border-gray-700 rounded-xl shadow-2xl p-5 sm:flex sm:items-center sm:justify-between gap-6">
        <div class="text-sm text-gray-300 leading-relaxed">
            <p>
                <strong class="text-white">We use cookies</strong> to keep you signed in, remember your preferences, and improve your experience.
                By continuing to use {{ config('app.name', 'Mines') }}, you consent to our use of essential and functional cookies.
                See our <a href="{{ $privacyUrl }}" class="underline text-amber-400 hover:text-amber-300">Privacy Policy</a>
                and <a href="{{ $termsUrl }}" class="underline text-amber-400 hover:text-amber-300">Terms of Service</a> for details.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-shrink-0 gap-3">
            <button
                @click="decline()"
                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
                Decline
            </button>
            <button
                @click="accept()"
                class="px-4 py-2 text-sm font-medium text-gray-900 bg-amber-400 hover:bg-amber-300 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
                Accept All
            </button>
        </div>
    </div>
</div>
