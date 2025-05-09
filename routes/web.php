<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/properties/{property}', function (\LBHurtado\Mortgage\Models\Property $property) {
    return response()->json([
        'code' => $property->code,
        'name' => $property->name,
    ]);
})->name('test-property');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
