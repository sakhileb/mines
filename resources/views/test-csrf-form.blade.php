<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Test Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold mb-4">CSRF Token Test</h1>
            
            <div class="mb-4 p-4 bg-blue-50 rounded">
                <p class="font-semibold">Current CSRF Token:</p>
                <code class="text-sm break-all">{{ csrf_token() }}</code>
            </div>
            
            <div class="mb-4 p-4 bg-green-50 rounded">
                <p class="font-semibold">Session ID:</p>
                <code class="text-sm break-all">{{ session()->getId() }}</code>
            </div>
            
            <form method="POST" action="{{ route('test.csrf.submit') }}" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Test Input:
                    </label>
                    <input 
                        type="text" 
                        name="test_field" 
                        value="Test value"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
                >
                    Submit Test Form
                </button>
            </form>
            
            <div class="mt-6 p-4 bg-yellow-50 rounded">
                <p class="font-semibold mb-2">What this tests:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>CSRF token generation</li>
                    <li>Session cookie creation</li>
                    <li>Form submission with CSRF validation</li>
                    <li>Session persistence across requests</li>
                </ul>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Session Configuration</h2>
            <dl class="space-y-2">
                <div class="flex">
                    <dt class="font-semibold w-48">Driver:</dt>
                    <dd>{{ config('session.driver') }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">Cookie Name:</dt>
                    <dd>{{ config('session.cookie') }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">Path:</dt>
                    <dd>{{ config('session.path') }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">Domain:</dt>
                    <dd>{{ config('session.domain') ?? 'null' }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">Secure:</dt>
                    <dd>{{ config('session.secure') ? 'true' : 'false' }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">Same Site:</dt>
                    <dd>{{ config('session.same_site') }}</dd>
                </div>
                <div class="flex">
                    <dt class="font-semibold w-48">HTTP Only:</dt>
                    <dd>{{ config('session.http_only') ? 'true' : 'false' }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                ← Back to Login
            </a>
        </div>
    </div>
</body>
</html>
