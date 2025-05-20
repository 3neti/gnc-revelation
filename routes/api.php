<?php

use LBHurtado\Mortgage\Http\Controllers\{AI\AIController,
    LendingInstitutionController,
    LoanMatchController,
    ProductController,
    ProductMatchController,
    PropertyController};
use LBHurtado\Mortgage\Actions\CreateLoanProfile;
use LBHurtado\Mortgage\Actions\OnboardLoanProfile;
use LBHurtado\Mortgage\Actions\ShowLoanProfile;
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
    Route::post('product-match', ProductMatchController::class)->name('api.v1.product-match');
    Route::post('mortgage-compute', MortgageComputationController::class)
        ->name('api.v1.mortgage-compute');
    Route::get('properties', [PropertyController::class, 'index'])->name('api.v1.properties');
    Route::get('/lending-institutions', [LendingInstitutionController::class, 'index']);
    Route::get('/lending-institutions/{key}', [LendingInstitutionController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index'])->middleware('web')->name('api.v1.products');
    Route::get('/products/{sku}', [ProductController::class, 'show'])->middleware('web')->name('api.v1.products.show');
    Route::post('/loan-profiles', CreateLoanProfile::class)->name('api.v1.loan-profiles.store');
    Route::get('/loan-profiles/{reference_code}', ShowLoanProfile::class)->name('api.v1.loan-profiles.show');
    Route::get('/loan-profiles/onboard/{reference_code}', OnboardLoanProfile::class)->name('api.v1.loan-profiles.onboard');
});

Route::get('/mortgage/docs/openapi.yaml', function () {
    $path = base_path('packages/lbhurtado/mortgage/resources/docs/openapi.yaml');
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/yaml',
    ]);
})->name('api.v1.mortgage.openapi.yaml');


Route::post('/ai/interact', [AIController::class, 'interact'])->name('api.ai.interact');
