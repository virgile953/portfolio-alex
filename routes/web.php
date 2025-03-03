<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\DB;

Route::get('/', function (): InertiaResponse {
    return Inertia::render('welcome');
})->name('home');

// Add this diagnostic route
Route::get('/db-test', function () {
    try {
        // Test basic connectivity
        $pdo = DB::connection()->getPdo();

        // Check which schemas we can access
        $schemas = DB::select("SELECT schema_name FROM information_schema.schemata");

        // Check which tables exist in the public schema
        $tables = DB::select(query: "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $projects = DB::select("SELECT * FROM project");
        return [
            'connection' => 'Connected to database: ' . DB::connection()->getDatabaseName(),
            'driver' => config('database.default'),
            'schemas' => $schemas,
            'tables' => $tables,
            ''=> $projects,
        ];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('tests', function () {
        return Inertia::render(component: 'tests');
    })->name('tests');
    Route::get('projectsManager', function () {
        return Inertia::render(component: 'projectsManager');
    })->name(name: 'Projects');

    // Move the projects resource route inside the auth middleware if you want it protected
    Route::resource('projectsManager', ProjectController::class);
    Route::resource('projects', ProjectController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
