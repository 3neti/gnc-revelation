<?php

use LBHurtado\Mortgage\Services\ProductMatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\{Product, Property};
use LBHurtado\Mortgage\Classes\Buyer;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

dataset('product matcher buyer scenarios', [
    'Single buyer qualifies for 30k income' => [
        'age' => 30,
        'income' => 30000.0,
        'additional_income' => null,
        'co_borrower' => null,
        'expected_min_count' => 1
    ],
    'Single buyer does not qualify for 10k income' => [
        'age' => 40,
        'income' => 10000.0,
        'additional_income' => null,
        'co_borrower' => null,
        'expected_min_count' => 0
    ],
    'Buyer with co-borrower qualifies' => [
        'age' => 35,
        'income' => 15000.0,
        'additional_income' => null,
        'co_borrower' => ['age' => 32, 'monthly_income' => 20000.0],
        'expected_min_count' => 1
    ],
    'Buyer with additional income qualifies' => [
        'age' => 28,
        'income' => 20000.0,
        'additional_income' => 15000.0,
        'co_borrower' => null,
        'expected_min_count' => 1
    ],
]);

it('returns filtered products based on buyer profile', function (
    int $age,
    float $income,
    ?float $additional_income,
    ?array $co_borrower,
    int $expected_min_count
) {

    $this->seed(ProductSeeder::class); // Assuming ~8 products with associated properties are seeded

    // Create a buyer instance with income, age, and other details
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($income)
    ;

    // Service instance
    $service = new ProductMatcherService();

    // Run the match service and get results
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: null, // No price limit in this test
        lending_institutions: null // No specific lending institutions in this test
    );

    // Assert results
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBeGreaterThanOrEqual($expected_min_count);

    if ($expected_min_count > 0) {
        expect($results->every(fn ($result) => $result->qualifies === true))->toBeTrue();
        $results->each(fn ($result) =>
        expect($result)->toHaveKeys([
            'qualifies',
            'reason',
            'product_code',
            'lending_institution',
            'interest_rate',
            'percent_down_payment',
            'percent_miscellaneous_fees',
            'total_contract_price',
            'income_requirement_multiplier',
            'balance_payment_term',
            'monthly_disposable_income',
            'present_value',
            'loanable_amount',
            'required_equity',
            'monthly_amortization',
            'miscellaneous_fees',
            'add_on_fees',
            'cash_out',
            'income_gap',
            'percent_down_payment_remedy',
            'required_income',
            'inputs'
        ])
        );
    }
})->with('product matcher buyer scenarios');

it('returns products when the buyer qualifies using factories', function () {
    // Step 1: Set up environment with controlled data
    $product1 = Product::factory()->create([
        'sku' => 'PROD001',
        'price' => 1_100_000
    ]);

    Property::factory()->create([
        'sku' => $product1->sku,
        'total_contract_price' => 1_200_000.00,
        'lending_institution' => 'rcbc'
        ]
    );

    $product2 = Product::factory()->create([
        'sku' => 'PROD002',
        'price' => 1_200_000
    ]);
    Property::factory()->create([
            'sku' => $product2->sku,
            'total_contract_price' => 1_250_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    // Step 2: Create a Buyer instance that qualifies
    $buyer = app(Buyer::class)
        ->setAge(48)
        ->setMonthlyGrossIncome(23_400); // Buyer income is greater than the requirement

    // Step 3: Run the matching service
    $service = new ProductMatcherService();
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: null, // No price limit
        lending_institutions: null // No lending institution filter
    );

    // Step 4: Assertions
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBe(1); // Only one qualifying product
    expect($results->first()->product_code)->toBe('PROD001'); // Check the correct product is returned
    expect($results->first()->qualifies)->toBeTrue(); // Confirm the product qualifies
});

it('returns no products when the buyer does not qualify', function () {
    // Step 1: Set up environment with controlled data
    $product = Product::factory()->create([
        'sku' => 'PROD001',
        'price' => 1_400_000.00
    ]);
    Property::factory()->create([
            'sku' => $product->sku,
            'total_contract_price' => 1_450_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    // Step 2: Create a Buyer instance that qualifies
    $buyer = app(Buyer::class)
        ->setAge(45)
        ->setMonthlyGrossIncome(25_000); // Buyer income is greater than the requirement

    // Step 3: Run the matching service
    $service = new ProductMatcherService();
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: null, // No price limit
        lending_institutions: null // No lending institution filter
    );

    // Step 4: Assertions
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBe(0); // No products should qualify
});

it('returns products when the buyer qualifies with a co-borrower', function () {
    // Step 1: Set up environment with controlled data
    $product = Product::factory()->create([
        'sku' => 'PROD003',
        'price' => 1_400_000.00
    ]);
    Property::factory()->create([
            'sku' => $product->sku,
            'total_contract_price' => 1_450_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    // Step 2: Create a Buyer instance that qualifies
    $buyer = app(Buyer::class)
        ->setAge(45)
        ->setMonthlyGrossIncome(25_000);
    $co_borrower = app(Buyer::class)
        ->setAge(50)
        ->setMonthlyGrossIncome(25_000);
    $buyer->addCoBorrower($co_borrower);

    // Step 3: Run the matching service
    $service = new ProductMatcherService();
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: null, // No price limit
        lending_institutions: null // No lending institution filter
    );

    // Step 4: Assertions
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBe(1); // No products should qualify
    expect($results->first()->product_code)->toBe('PROD003'); // Check the correct product is returned
    expect($results->first()->qualifies)->toBeTrue(); // Confirm the product qualifies
});

it('returns products when the buyer qualifies with additional income', function () {
    // Step 1: Set up environment with controlled data
    $product = Product::factory()->create([
        'sku' => 'PROD004',
        'price' => 1_400_000.00
    ]);
    Property::factory()->create([
            'sku' => $product->sku,
            'total_contract_price' => 1_450_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    // Step 2: Create a Buyer instance that qualifies
    $buyer = app(Buyer::class)
        ->setAge(45)
        ->setMonthlyGrossIncome(25_000)
        ->addOtherSourcesOfIncome('Side Hustle', 2_100);
    ;

    // Step 3: Run the matching service
    $service = new ProductMatcherService();
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: null, // No price limit
        lending_institutions: null // No lending institution filter
    );

    // Step 4: Assertions
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBe(1); // No products should qualify
    expect($results->first()->product_code)->toBe('PROD004'); // Check the correct product is returned
    expect($results->first()->qualifies)->toBeTrue(); // Confirm the product qualifies
});

it('filters products based on price limits', function () {
    // Step 1: Set up environment with controlled data
    $product1 = Product::factory()->create([
        'sku' => 'PROD005',
        'price' => 1_100_000
    ]);

    Property::factory()->create([
            'sku' => $product1->sku,
            'total_contract_price' => 1_200_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    $product2 = Product::factory()->create([
        'sku' => 'PROD006',
        'price' => 1_500_000
    ]);
    Property::factory()->create([
            'sku' => $product2->sku,
            'total_contract_price' => 1_550_000.00,
            'lending_institution' => 'rcbc'
        ]
    );

    // Step 2: Create a Buyer instance that qualifies
    $buyer = app(Buyer::class)
        ->setAge(48)
        ->setMonthlyGrossIncome(40_000); // Buyer income is greater than the requirement

    // Step 3: Run the matching service
    $service = new ProductMatcherService();
    $results = $service->matchQualifiedOnly(
        buyer: $buyer,
        price_limit: 1_100_000,
        lending_institutions: null // No lending institution filter
    );

    // Step 4: Assertions
    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->count())->toBe(1); // Only one qualifying product
    expect($results->first()->product_code)->toBe('PROD005'); // Check the correct product is returned
    expect($results->first()->qualifies)->toBeTrue(); // Confirm the product qualifies
});
