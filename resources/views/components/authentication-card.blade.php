<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-900 via-slate-800 to-amber-900">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-slate-800/50 backdrop-blur-sm border border-slate-700 shadow-2xl overflow-hidden sm:rounded-xl">
        {{ $slot }}
    </div>
</div>
