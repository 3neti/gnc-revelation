<?php

use Illuminate\Support\Facades\{File, Route};
use Illuminate\Support\Facades\Response;
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

// routes/web.php or api.php
Route::get('/openapi.yaml', fn () => response()->file(base_path('openapi.yaml'), [
    'Content-Type' => 'text/yaml'
]));

Route::get('/openapi.yaml', function () {
    $path = base_path('packages/lbhurtado/mortgage/resources/docs/openapi.yaml');
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/yaml',
    ]);
})->name('openapi.yaml');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
