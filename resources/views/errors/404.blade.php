<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 – Page Not Found | {{ config('app.name', 'Mines') }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' fill='%23f59e0b' rx='15'/><path d='M20 45 L20 30 L35 37 L35 52 L20 45 M35 52 L50 45 L50 60 L35 67 L35 52 M50 60 L65 53 L65 68 L50 75 L50 60 M35 37 L50 30 L50 45 L35 52 L35 37 M50 45 L65 38 L65 53 L50 60 L50 45 M50 30 L65 23 L80 30 L65 38 L50 30' fill='%231e293b' stroke='%231e293b' stroke-width='2'/></svg>">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: #111827; color: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .container { max-width: 480px; text-align: center; }
        .code { font-size: 6rem; font-weight: 600; color: #f59e0b; line-height: 1; }
        h1 { font-size: 1.5rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        p { color: #9ca3af; margin-bottom: 2rem; line-height: 1.6; }
        a { display: inline-block; padding: 0.625rem 1.5rem; background: #f59e0b; color: #111827; border-radius: 0.5rem; font-weight: 600; text-decoration: none; transition: background 0.15s; }
        a:hover { background: #d97706; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ url('/') }}">Go to Dashboard</a>
    </div>
</body>
</html>
