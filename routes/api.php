<?php

use LBHurtado\Mortgage\Http\Controllers\{LoanMatchController, PropertyController};
use LBHurtado\Mortgage\Http\Controllers\MortgageComputationController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php

Route::prefix('v1')->group(function () {
    Route::post('loan-match', LoanMatchController::class)->name('api.v1.loan-match');
    Route::post('mortgage-compute', MortgageComputationController::class)
        ->name('api.v1.mortgage-compute');
    Route::get('properties', [PropertyController::class, 'index'])->name('api.v1.properties');
});

Route::get('/mortgage/docs/openapi.yaml', function () {
    $path = base_path('packages/lbhurtado/mortgage/resources/docs/openapi.yaml');
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/yaml',
    ]);
})->name('api.v1.mortgage.openapi.yaml');


