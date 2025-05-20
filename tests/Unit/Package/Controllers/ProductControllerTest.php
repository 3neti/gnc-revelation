<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\{Product, Property};
use Database\Seeders\ProductSeeder;

uses(RefreshDatabase::class);

it('returns a list of products with their properties', function () {
    $this->seed(ProductSeeder::class);

    expect(app(Product::class)->all())->toHaveCount(3);
    $response = $this->getJson(route('api.v1.products'));

    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'sku',
                'name',
                'brand',
                'category',
                'description',
                'price',
                'properties' => [
                    '*' => [
                        'sku',
                        'code',
                        'name',
                        'type',
                        'cluster',
                        'status',
                        'project_code',
                        'total_contract_price',
                        'appraisal_value',
                        'development_type',
                        'development_form',
                        'housing_type',
                        'percent_loanable_value',
                        'percent_miscellaneous_fees',
                        'required_buffer_margin',
                        'lending_institution',
                        'income_requirement_multiplier',
                    ]
                ]
            ]
        ]);

    expect($response->json())->toHaveCount(3); // seeded 3 products
});

it('returns a single product by sku with its properties', function () {
    $this->seed(ProductSeeder::class);

    $sku = 'product-b';
    $response = $this->getJson(route('api.v1.products.show', ['sku' => $sku]));

    $response->assertOk()
        ->assertJson([
            'sku' => 'product-b',
            'name' => 'Product B',
            'brand' => 'Brand A',
            'category' => 'Category Yellow',
            'description' => 'Product B description',
            'price' => 1_250_000,
        ])
        ->assertJsonStructure([
            'sku',
            'name',
            'brand',
            'category',
            'description',
            'price',
            'properties' => [
                '*' => [
                    'sku',
                    'code',
                    'name',
                    'type',
                    'cluster',
                    'status',
                    'project_code',
                    'total_contract_price',
                    'appraisal_value',
                        'development_type',
                        'development_form',
                        'housing_type',
                    'percent_loanable_value',
                    'percent_miscellaneous_fees',
                        'required_buffer_margin',
                    'lending_institution',
                    'income_requirement_multiplier',
                ]
            ]
        ]);
});

it('returns 404 when product is not found', function () {
    $this->seed(ProductSeeder::class);
    $response = $this->getJson(route('api.v1.products.show', ['sku' => 'nonexistent-sku']));

    $response->assertNotFound()
        ->assertJson([
            'message' => "Product 'nonexistent-sku' not found for the given lending institution."
        ]);
});

it('filters the products list by lending institution if set in the session', function () {
    // Create test data
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    Property::factory()->create(['sku' => $product1->sku, 'meta' => ['lending_institution' => 'hdmf']]);
    Property::factory()->create(['sku' => $product2->sku, 'meta' => ['lending_institution' => 'rcbc']]);

    // Simulate session value for lending institution
    $this->withSession(['lending_institution' => 'hdmf']);

    // Call the index endpoint
    $response = $this->getJson(route('api.v1.products'));

    // Assertions
    $response->assertStatus(200);
    $response->assertJsonCount(1); // Only returns products with 'hdmf'
    $response->assertJsonFragment(['sku' => 'PROD001']);
});

it('filters the products list by lending institution and product SKU', function () {
    expect(Property::all())->toBeEmpty();
    expect(Product::all())->toBeEmpty();

    // Create test data
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);
    $product3 = Product::factory()->create(['sku' => 'PROD003']);

    expect(Product::all())->toHaveCount(3);

    Property::factory()->create(['sku' => $product1->sku, 'lending_institution' => 'hdmf']);
    Property::factory()->create(['sku' => $product2->sku, 'lending_institution' => 'hdmf']);
    Property::factory()->create(['sku' => $product3->sku, 'lending_institution' => 'rcbc']);

    expect(Property::all())->toHaveCount(3);

    // Simulate session value for lending institution
    $this->withSession(['lending_institution' => 'hdmf']);

    // Call the index endpoint with an additional SKU filter
    $response = $this->getJson(route('api.v1.products', ['sku' => 'PROD001']));

    // Assertions
    $response->assertStatus(200);
    $response->assertJsonCount(1); // Only returns one product with SKU 'PROD001' and lending_institution 'hdmf'
    $response->assertJsonFragment(['sku' => 'PROD001']);
});

it('lists all products if lending institution is not set', function () {
    // Create test data
    Product::factory()->create(['sku' => 'PROD001']);
    Product::factory()->create(['sku' => 'PROD002']);

    // No session value
    $response = $this->getJson(route('api.v1.products'));

    // Assertions
    $response->assertStatus(200);
    $response->assertJsonCount(2); // Returns all products
    $response->assertJsonFragment(['sku' => 'PROD001']);
    $response->assertJsonFragment(['sku' => 'PROD002']);
});

it('returns products without properties if no properties are linked', function () {
    // Create a product without property linkage
    $product = Product::factory()->create(['sku' => 'PROD001']);

    $response = $this->getJson(route('api.v1.products'));

    $response->assertStatus(200);
    $response->assertJsonFragment(['sku' => 'PROD001']);
    $response->assertJsonCount(0, '0.properties'); // Ensure properties collection is empty
});

it('returns no products when lending institution in session is invalid', function () {
    // Create properties linked to lending institutions
    $product = Product::factory()->create(['sku' => 'PROD001']);
    Property::factory()->create(['sku' => $product->sku, 'lending_institution' => 'hdmf']);

    $this->withSession(['lending_institution' => 'invalid-institution']);

    $response = $this->getJson(route('api.v1.products'));

    $response->assertStatus(200);
    $response->assertJsonCount(0); // Returns no products
});

it('returns empty when SKU does not match the session lending institution', function () {
    // Create test data
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    Property::factory()->create(['sku' => $product1->sku, 'lending_institution' => 'hdmf']);

    $this->withSession(['lending_institution' => 'rcbc']); // Different lending institution

    $response = $this->getJson(route('api.v1.products', ['sku' => 'PROD001']));

    $response->assertStatus(200);
    $response->assertJsonCount(0); // No products returned
});

it('handles large datasets efficiently', function () {
    // Generate a large dataset
    $products = Product::factory(100)->create();
    foreach ($products as $product) {
        Property::factory()->create([
            'sku' => $product->sku,
            'lending_institution' => 'hdmf',
        ]);
    }

    $this->withSession(['lending_institution' => 'hdmf']);

    $response = $this->getJson(route('api.v1.products'));

    $response->assertStatus(200);
    $response->assertJsonCount(100); // Ensure all 100 products are returned
});

it('returns all products when SKU is missing and no lending institution is set', function () {
    // Create test data
    Product::factory()->count(3)->create();

    // No session or SKU in request
    $response = $this->getJson(route('api.v1.products'));

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

it('filters products with properties belonging to multiple lending institutions', function () {
    $product1 = Product::factory()->create(['sku' => 'PROD001']);
    $product2 = Product::factory()->create(['sku' => 'PROD002']);

    // Properties belong to multiple lending institutions
    Property::factory()->create(['sku' => $product1->sku, 'lending_institution' => 'hdmf']);
    Property::factory()->create(['sku' => $product1->sku, 'lending_institution' => 'rcbc']);
    Property::factory()->create(['sku' => $product2->sku, 'lending_institution' => 'hdmf']);

    $this->withSession(['lending_institution' => 'hdmf']);
    $response = $this->getJson(route('api.v1.products'));

    $response->assertStatus(200);
    $response->assertJsonCount(2); // Both products should match
    $response->assertJsonFragment(['sku' => 'PROD001']);
    $response->assertJsonFragment(['sku' => 'PROD002']);
});
