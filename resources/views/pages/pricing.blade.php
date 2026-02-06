@extends('layouts.app')
@section('title', 'Pricing')
@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-pink-500 mb-6">Pricing</h1>
    <div class="bg-gray-800 rounded-xl p-8 shadow-lg flex flex-col gap-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-900 rounded-lg p-6 border border-gray-700 flex flex-col items-center">
                <h2 class="text-2xl font-semibold text-white mb-2">Starter</h2>
                <p class="text-3xl font-bold text-pink-400 mb-4">R2,500<span class="text-base font-normal text-gray-400">/mo</span></p>
                <ul class="text-gray-300 mb-4 space-y-2 text-sm">
                    <li>Up to 10 machines</li>
                    <li>1 mining site</li>
                    <li>Email support</li>
                </ul>
                <a href="#" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded transition">Get Started</a>
            </div>
            <div class="bg-gray-900 rounded-lg p-6 border-2 border-pink-500 flex flex-col items-center scale-105">
                <h2 class="text-2xl font-semibold text-white mb-2">Professional</h2>
                <p class="text-3xl font-bold text-pink-400 mb-4">R7,500<span class="text-base font-normal text-gray-400">/mo</span></p>
                <ul class="text-gray-300 mb-4 space-y-2 text-sm">
                    <li>Up to 50 machines</li>
                    <li>Up to 5 mining sites</li>
                    <li>Priority support</li>
                    <li>API access</li>
                </ul>
                <a href="#" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded transition">Get Started</a>
            </div>
            <div class="bg-gray-900 rounded-lg p-6 border border-gray-700 flex flex-col items-center">
                <h2 class="text-2xl font-semibold text-white mb-2">Enterprise</h2>
                <p class="text-3xl font-bold text-pink-400 mb-4">Custom</p>
                <ul class="text-gray-300 mb-4 space-y-2 text-sm">
                    <li>Unlimited machines & sites</li>
                    <li>Dedicated account manager</li>
                    <li>Custom integrations</li>
                    <li>24/7 support</li>
                </ul>
                <a href="#" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded transition">Contact Sales</a>
            </div>
        </div>
    </div>
</div>
@endsection