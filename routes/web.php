<?php

use Illuminate\Support\Facades\Route;
use App\Models\Machine;
use App\Models\Geofence;
use App\Models\Report;
use App\Http\Controllers\ReportController;

// Include test routes for session/CSRF debugging (remove in production)
if (config('app.debug')) {
    require __DIR__.'/test-session.php';
}

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ensure_team',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Fleet Management
    Route::get('/fleet', function () {
        return view('fleet.index');
    })->name('fleet');

    // Specific fleet routes must come before parameterized routes
    Route::get('/fleet/replay', App\Livewire\FleetMovementReplay::class)
        ->name('fleet.replay');

    Route::get('/fleet/route-planning', App\Livewire\RoutePlanning::class)
        ->name('fleet.route-planning');

    // Parameterized route comes last
    Route::get('/fleet/{machine}', function (Machine $machine) {
        return view('fleet.show', ['machine' => $machine]);
    })->name('fleet.show');

    // Live Map
    Route::get('/map', function () {
        return view('map.index');
    })->name('map');

    // Geofences
    Route::get('/geofences', function () {
        return view('geofences.index');
    })->name('geofences');

    Route::get('/geofences/{geofence}', function (Geofence $geofence) {
        return view('geofences.show', ['geofence' => $geofence]);
    })->name('geofences.show');

    // Mine Areas
    Route::get('/mine-areas', function () {
        return view('mine-areas.index');
    })->name('mine-areas');

    Route::get('/mine-areas/{mineArea}', App\Livewire\MineAreaDetail::class)
        ->name('mine-areas.show');

    // Reports
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports');

    // Live report generator (Livewire)
    Route::get('/reports/generate', function () {
        return view('reports.generate');
    })->name('report-generator');

    Route::get('/reports/{report}', function (Report $report) {
        return view('reports.show', ['report' => $report]);
    })->name('reports.show');

    // Reports view 2 (scope selectors)
    Route::get('/reports/view-2', [ReportController::class, 'view2'])->name('reports.view2');
    // Simple generate endpoint (GET form) — moved to avoid path conflict with Livewire generator
    Route::get('/reports/generate/simple', [ReportController::class, 'generate'])->name('reports.generate');

    // Alerts
    Route::get('/alerts', App\Livewire\Alerts::class)
        ->name('alerts');

    // Production Dashboard
    Route::get('/production', App\Livewire\ProductionDashboard::class)
        ->name('production');

    // Fuel Management
    Route::get('/fuel', App\Livewire\FuelManagement::class)
        ->name('fuel');

    // Maintenance & Health
    Route::get('/maintenance', App\Livewire\MaintenanceDashboard::class)
        ->name('maintenance');

    // AI Optimization Center
    Route::get('/ai-optimization', App\Livewire\AIOptimizationDashboard::class)
        ->name('ai-optimization');
    Route::get('/ai-analytics', App\Livewire\AIAnalytics::class)
        ->name('ai-analytics');

    // Documentation
    Route::get('/documentation', App\Livewire\Documentation::class)
        ->name('documentation');

    // Integrations
    Route::get('/integrations', function () {
        return view('integrations.index');
    })->name('integrations');

    Route::get('/integrations/{integration}', function () {
        return view('integrations.show');
    })->name('integrations.show');

    // Billing & Subscriptions
    Route::get('/billing', App\Livewire\BillingPortal::class)
        ->name('billing.index');

    Route::get('/billing/success', function () {
        return redirect()->route('billing.index')->with('success', 'Subscription activated successfully!');
    })->name('billing.success');

    // Settings
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');

    Route::get('/team/settings', function () {
        return view('team.settings');
    })->name('team.settings');
});

// Stripe Webhooks (no auth required)
Route::post('/webhooks/stripe', [App\Http\Controllers\WebhookController::class, 'handleStripe'])
    ->name('webhooks.stripe');

// Public marketing/outer pages
Route::view('/features', 'pages.features')->name('features');
Route::view('/capabilities', 'pages.capabilities')->name('capabilities');
Route::view('/pricing', 'pages.pricing')->name('pricing');

// Core features detail pages
Route::prefix('core-features')->group(function () {
    Route::view('/fleet-tracking', 'pages.core-features.fleet-tracking')->name('core-features.fleet-tracking');
    Route::view('/maintenance', 'pages.core-features.maintenance')->name('core-features.maintenance');
    Route::view('/fuel', 'pages.core-features.fuel')->name('core-features.fuel');
});

