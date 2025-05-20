<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\{Product, Property};
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Whitecube\Price\Price;
use Brick\Money\Money;

uses(RefreshDatabase::class);

it('creates a product via factory', function () {
    $product = Product::factory()->create();

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->sku)->toBeString()
        ->and($product->price)->toBeInstanceOf(Price::class);
});

it('stores price in minor units', function () {
    $product = Product::factory()->create(['price' => 1234567.89]);

    $raw = $product->getRawOriginal('price');
    expect($raw)->toBe(123456789); // 1234567.89 in minor units
});

it('retrieves price as a Price object', function () {
    $product = Product::factory()->create(['price' => 1234567.89]);

    expect($product->price)->toBeInstanceOf(Price::class)
        ->and($product->price->inclusive()->getAmount()->toFloat())->toBeCloseTo(1234567.89, 0.01);
});

it('accepts a Price object directly', function () {
    $price = MoneyFactory::price(999_999.99);
    $product = Product::factory()->create(['price' => $price]);

    expect($product->price)->toBeInstanceOf(Price::class)
        ->and($product->price->inclusive()->getAmount()->toFloat())->toBeCloseTo(999_999.99, 0.01)
        ->and($product->getRawOriginal('price'))->toBe(99999999);
});

it('accepts a Brick Money object directly', function () {
    $money = Money::of(1_234_567.89, 'PHP');
    $product = Product::factory()->create(['price' => $money]);
    $product->refresh();
    expect($product->price->inclusive()->getAmount()->toFloat())->toBeCloseTo(1234567.89, 0.01)
        ->and($product->getRawOriginal('price'))->toBe(123456789);
});

it('has many properties using sku as the local and foreign key', function () {
    // Create a Product with SKU
    $product = Product::factory()->create([
        'sku' => 'PROD001',
    ]);

    // Create related Properties using the product's SKU
    Property::factory()->count(3)->create([
        'sku' => $product->sku,
    ]);

    // Assertions
    expect($product->properties)
        ->toHaveCount(3) // Ensures the product has exactly 3 properties
        ->and($product->properties->pluck('sku')->unique()->first())->toBe('PROD001'); // Checks the foreign key
});

it('filters products by lending institution using the forLendingInstitution method', function () {
    // Create products with properties tied to different lending institutions
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    Property::factory()->create([
        'sku' => $product1->sku,
        'meta' => ['lending_institution' => 'hdmf'], // For product 1
    ]);

    Property::factory()->create([
        'sku' => $product2->sku,
        'meta' => ['lending_institution' => 'rcbc'], // For product 2
    ]);

    // Explicitly query for products associated with 'rcbc'
    $products = Product::forLendingInstitution('rcbc')->get();

    expect($products)
        ->toHaveCount(1) // Only one product should match
        ->and($products->first()->sku)->toBe('PROD002'); // Correct product is retrieved
});

it('filters products by lending institution via global scope', function () {
    // Create products with properties tied to different lending institutions
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    Property::factory()->create([
        'sku' => $product1->sku,
        'meta' => ['lending_institution' => 'hdmf'], // For product 1
    ]);

    Property::factory()->create([
        'sku' => $product2->sku,
        'meta' => ['lending_institution' => 'rcbc'], // For product 2
    ]);

    // Set the lending_institution in the session
    session(['lending_institution' => 'hdmf']);

    // Retrieve all products, expect only the product associated with 'hdmf'
    $products = Product::all();

    expect($products)
        ->toHaveCount(1) // Only one product should be retrieved
        ->and($products->first()->sku)->toBe('PROD001'); // Correct product is retrieved
});

it('dynamically applies and removes the global scope based on the lending_institution session value', function () {
    // Create two products with different lending institutions
    $productWithHDMF = Product::factory()->create(['sku' => 'PROD001']);
    $productWithRCBC = Product::factory()->create(['sku' => 'PROD002']);

    Property::factory()->create([
        'sku' => $productWithHDMF->sku,
        'meta' => ['lending_institution' => 'hdmf'],
    ]);

    Property::factory()->create([
        'sku' => $productWithRCBC->sku,
        'meta' => ['lending_institution' => 'rcbc'],
    ]);

    // Step 1: Set the lending institution in the session
    session(['lending_institution' => 'hdmf']);

    // Products should be filtered by 'hdmf'
    $productsWithHDMF = Product::all();

    expect($productsWithHDMF)
        ->toHaveCount(1) // Only 'hdmf' products should be retrieved
        ->and($productsWithHDMF->first()->sku)->toBe('PROD001');

    // Step 2: Dynamically remove the lending institution from the session
    session()->forget('lending_institution');

    // Products should not be filtered anymore, so all products should be returned
    $allProducts = Product::all();

    expect($allProducts)
        ->toHaveCount(2) // Both products should be retrieved
        ->and($allProducts->pluck('sku')->toArray())->toBe(['PROD001', 'PROD002']);
});

it('filters products by TCP and lending institution', function () {
    // Step 1: Create two products
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    // Step 2: Assign properties to products with different lending institutions and prices
    Property::factory()->create([
        'sku' => $product1->sku,
        'meta' => ['lending_institution' => 'hdmf', 'tcp' => 5000000], // Product 1 details
    ]);

    Property::factory()->create([
        'sku' => $product2->sku,
        'meta' => ['lending_institution' => 'rcbc', 'tcp' => 3000000], // Product 2 details
    ]);

    // Step 3: Query products based on TCP (e.g., less than or equal to 4,000,000) and lending institution
    $products = Product::query()
        ->whereHas('properties', fn($query) => $query
            ->where('meta->lending_institution', 'hdmf') // Filter by lending institution
            ->where('meta->tcp', '<=', 4000000) // Filter TCP <= 4,000,000
        )
        ->get();

    // Step 4: Assertions
    expect($products)
        ->toHaveCount(0); // No products match this filter
});

it('successfully retrieves products by TCP and lending institution', function () {
    // Step 1: Create products
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    // Step 2: Assign properties
    Property::factory()->create([
        'sku' => $product1->sku,
        'meta' => ['lending_institution' => 'hdmf', 'tcp' => 4000000], // Matches criteria
    ]);

    Property::factory()->create([
        'sku' => $product2->sku,
        'meta' => ['lending_institution' => 'rcbc', 'tcp' => 3000000], // Does not match
    ]);

    // Step 3: Query products
    $products = Product::query()
        ->whereHas('properties', fn($query) => $query
            ->where('meta->lending_institution', 'hdmf') // Lending institution filter
            ->where('meta->tcp', '<=', 4000000) // TCP filter
        )
        ->get();

    // Step 4: Assertions
    expect($products)
        ->toHaveCount(1) // Only one product should match
        ->and($products->first()->sku)->toBe('PROD001'); // Correct product
});

it('filters products by price in major units via model scope', function () {
    // Step 1: Create products with different prices in minor units
    Product::factory()->create(['sku' => 'PROD001', 'price' => 5000000]); // Price: 5,000,000
    Product::factory()->create(['sku' => 'PROD002', 'price' => 3000000]); // Price: 3,000,000
    Product::factory()->create(['sku' => 'PROD003', 'price' => 7000000]); // Price: 7,000,000

    // Step 2: Use the new scope to filter products by price in major units
    $priceLimitInMajorUnits = 6000000; // 6,000,000
    $products = Product::filterByPrice($priceLimitInMajorUnits)->get();

    // Step 3: Assertions
    expect($products)
        ->toHaveCount(2) // Products 1 and 2 match the price filter
        ->and($products->pluck('sku')->toArray())->toBe(['PROD001', 'PROD002']); // Matching products
});

it('filters products by price and lending institution array', function () {
    // Step 1: Create products with different prices
    $product1 = Product::factory()->create(['sku' => 'PROD001', 'price' => 5000000]); // Price: 5,000,000
    $product2 = Product::factory()->create(['sku' => 'PROD002', 'price' => 3000000]); // Price: 3,000,000
    $product3 = Product::factory()->create(['sku' => 'PROD003', 'price' => 7000000]); // Price: 7,000,000

    // Step 2: Assign properties to products with different lending institutions
    Property::factory()->create(['sku' => $product1->sku, 'meta' => ['lending_institution' => 'hdmf']]);
    Property::factory()->create(['sku' => $product2->sku, 'meta' => ['lending_institution' => 'rcbc']]);
    Property::factory()->create(['sku' => $product3->sku, 'meta' => ['lending_institution' => 'cbc']]);

    // Define filters
    $priceLimit = 6000000; // E.g.: Products cheaper or equal to 6,000,000
    $lendingInstitutions = ['hdmf', 'rcbc'];

    // Step 3: Filter products using defined scopes
    $products = Product::query()
        ->filterByPrice($priceLimit) // Use the Product scope for price filtering
        ->whereHas('properties', fn ($query) => $query->forLendingInstitution($lendingInstitutions)) // Use the Property scope for lending institutions
        ->get();

    // Step 4: Assertions for filtered results
    expect($products)
        ->toHaveCount(2) // Products 1 and 2 should match the conditions
        ->and($products->pluck('sku')->toArray())->toBe(['PROD001', 'PROD002']); // Matching SKUs
});

it('includes all products when price limit is null', function () {
    // Step 1: Create products with varying prices
    Product::factory()->create(['sku' => 'PROD001', 'price' => 2000000]); // 2,000,000
    Product::factory()->create(['sku' => 'PROD002', 'price' => 4000000]); // 4,000,000
    Product::factory()->create(['sku' => 'PROD003', 'price' => 6000000]); // 6,000,000

    // Step 2: Use the scopeFilterByPrice() with a null price limit
    $products = Product::filterByPrice(null)->get();

    // Step 3: Assertions
    expect($products)
        ->toHaveCount(3) // All products should be included
        ->and($products->pluck('sku')->toArray())->toBe(['PROD001', 'PROD002', 'PROD003']); // SKUs of all created products
});
