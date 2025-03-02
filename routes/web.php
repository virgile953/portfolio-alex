<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Controllers\ProjectController;

Route::get('/', function (): InertiaResponse {
    return Inertia::render('welcome');
})->name('home');

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
