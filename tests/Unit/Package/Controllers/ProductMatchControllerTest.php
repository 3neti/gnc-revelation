<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\{Product, Property};

/**
 * Test ProductMatchController via API route
 */
uses(RefreshDatabase::class);

it('matches products for a valid request', function () {
    // Step 1: Set up test data
    $product = Product::factory()->create(['sku' => 'PROD001', 'price' => 1_100_000]);
    Property::factory()->create([
        'sku' => 'PROD001',
        'total_contract_price' => 1_200_000.00,
        'lending_institution' => 'rcbc',
    ]);

    // Step 2: Make a request to the controller
    $payload = [
        'age' => 30,
        'monthly_income' => 50_000,
        'additional_income' => [
            'name' => 'Side Hustle',
            'amount' => 10_000,
        ],
        'co_borrower' => [
            'age' => 28,
            'monthly_income' => 20_000,
        ],
        'lending_institution' => 'rcbc',
        'price_limit' => 1_200_000,
    ];

    $response = $this->postJson(route('api.v1.product-match'), $payload);

    // Step 3: Assertions
    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data',
    ]);
    $response->assertJsonPath('success', true);
    $this->assertCount(1, $response->json('data')); // Ensure one product matched
    $this->assertEquals('PROD001', $response->json('data.0.product_code')); // Matched product is correct
});

it('returns validation errors for an invalid request', function () {
    // Step 1: Send a request with missing required fields
    $payload = [
        'age' => 17, // Age too young
        'monthly_income' => 500, // Monthly income too low
        'lending_institution' => 'unknown', // Invalid lending institution
    ];

    $response = $this->postJson(route('api.v1.product-match'), $payload);

    // Step 2: Assertions
    $response->assertStatus(422); // HTTP Unprocessable Entity
    $response->assertJsonValidationErrors([
        'age',
        'monthly_income',
        'lending_institution',
    ]);
});

it('returns no matches when no products qualify', function () {
    // Step 1: Set up test data
    $product = Product::factory()->create(['sku' => 'PROD002', 'price' => 3_000_000]);
    Property::factory()->create([
        'sku' => 'PROD002',
        'total_contract_price' => 3_500_000.00,
        'lending_institution' => 'hdmf',
    ]);

    // Step 2: Make a request to the controller
    $payload = [
        'age' => 40,
        'monthly_income' => 15_000, // Income too low for the product
    ];

    $response = $this->postJson(route('api.v1.product-match'), $payload);

    // Step 3: Assertions
    $response->assertOk();
    $response->assertJsonPath('success', true);
    $this->assertCount(0, $response->json('data')); // No products qualify
});

it('ignores additional_income and co_borrower when not provided', function () {
    // Step 1: Set up test data
    $product = Product::factory()->create(['sku' => 'PROD003', 'price' => 1_200_000]);
    Property::factory()->create([
        'sku' => 'PROD003',
        'total_contract_price' => 1_250_000.00,
        'lending_institution' => 'cbc',
    ]);

    // Step 2: Make a request to the controller
    $payload = [
        'age' => 50,
        'monthly_income' => 60_000, // Sufficient income to qualify
        'lending_institution' => 'cbc', // Matching institution
    ];

    $response = $this->postJson(route('api.v1.product-match'), $payload);

    // Step 3: Assertions
    $response->assertOk();
    $response->assertJsonPath('success', true);
    $this->assertCount(1, $response->json('data')); // One match found
    $this->assertEquals('PROD003', $response->json('data.0.product_code')); // Correct product
});

it('matches multiple products for a valid request', function () {
    // Step 1: Set up test data
    $product1 = Product::factory()->create(['sku' => 'PROD001', 'price' => 1_100_000]);
    Property::factory()->create([
        'sku' => $product1->sku,
        'total_contract_price' => 1_200_000.00,
        'lending_institution' => 'rcbc',
    ]);

    $product2 = Product::factory()->create(['sku' => 'PROD002', 'price' => 1_150_000]);
    Property::factory()->create([
        'sku' => $product2->sku,
        'total_contract_price' => 1_300_000.00,
        'lending_institution' => 'rcbc',
    ]);

    $product3 = Product::factory()->create(['sku' => 'PROD003', 'price' => 1_175_000]);
    Property::factory()->create([
        'sku' => $product3->sku,
        'total_contract_price' => 1_400_000.00,
        'lending_institution' => 'rcbc',
    ]);

    // Step 2: Make a request to the controller
    $payload = [
        'age' => 35,
        'monthly_income' => 100_000, // High enough to qualify for multiple products
        'lending_institution' => 'rcbc',
        'price_limit' => 1_150_000, // Price limit that allows all three products
    ];

    $response = $this->postJson(route('api.v1.product-match'), $payload);

    // Step 3: Assertions
    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data',
    ]);

    $response->assertJsonPath('success', true);

    // Ensure three products matched
    $this->assertCount(2, $response->json('data'));

    // Assert on product SKUs to ensure correct matches
    $matchedSkus = collect($response->json('data'))->pluck('product_code')->toArray();
    $this->assertContains('PROD001', $matchedSkus);
    $this->assertContains('PROD002', $matchedSkus);
});
